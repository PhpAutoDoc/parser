<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse;

use PhpAutoDoc\Parser\Parse\Event\SourceFileFoundEvent;
use PhpAutoDoc\Parser\PhpAutoDoc;
use SetBased\Stratum\Common\Helper\SourceFinderHelper;

/**
 * The main parser. The main parser must parse all given PHP source files and store the data of the PHP sources into the
 * Store (i.e. the SQLite database).
 */
class MainParser
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @var string
   */
  private $externalSources;

  /**
   * The options.
   *
   * @var array
   */
  private $options;

  /**
   * @var string
   */
  private $projectSources;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Object constructor.
   *
   * @param string $externalSources
   * @param string $projectSources
   * @param array  $options The options.
   */
  public function __construct(string $externalSources, string $projectSources, array $options)
  {
    $this->externalSources = $externalSources;
    $this->projectSources  = $projectSources;
    $this->options         = $options;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses all given PHP source files and store the data of the PHP sources into the Store (i.e. the SQLite database).
   *
   * @return int
   */
  public function parse(): int
  {
    $this->prepare();
    $this->findExternalSources();
    $this->findProjectSources();

    PhpAutoDoc::$eventDispatcher->dispatch();

    $this->aftercare();

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Performance aftercare.
   */
  private function aftercare()
  {
    PhpAutoDoc::$dl->padFileDeleteAllUnseen();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Find sources outside of the project to be documented but might be used directly or indirectly by the project to be
   * documented
   */
  private function findExternalSources(): void
  {
    $helper = new SourceFinderHelper(getcwd());
    $paths  = $helper->findSources($this->externalSources);

    foreach ($paths as $path)
    {
      PhpAutoDoc::$eventDispatcher->notify(new SourceFileFoundEvent($path, false));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Find sources of the project to be documented.
   */
  private function findProjectSources(): void
  {
    $helper = new SourceFinderHelper(getcwd());
    $paths  = $helper->findSources($this->projectSources);

    foreach ($paths as $path)
    {
      PhpAutoDoc::$eventDispatcher->notify(new SourceFileFoundEvent($path, true));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Performs preparations.
   */
  private function prepare(): void
  {
    PhpAutoDoc::$dl->padFileUpdateAllUnseen();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
