<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Event;

use PhpAutoDoc\Parser\PhpAutoDoc;

/**
 * Event triggered when (the main parser) has found a PHP source file.
 */
class NewSourceFileFoundEvent extends SourceFileFoundEvent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The source code.
   *
   * @var string
   */
  private $contents;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $path      The path to the source file.
   * @param bool   $isProject True if and only if the source does belong to the project.
   * @param string $contents  The source code.
   */
  public function __construct(string $path, bool $isProject, string $contents)
  {
    parent::__construct($path, $isProject);

    $this->contents = $contents;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the source code.
   *
   * @return string
   */
  public function contents(): string
  {
    return $this->contents;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
