<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Command;

use PhpAutoDoc\Parser\DataLayer;
use PhpAutoDoc\Parser\PhpAutoDoc;
use PhpAutoDoc\Parser\Plaisio\AutoDocEventDispatcher;
use PhpAutoDoc\Parser\Plaisio\CliKernel;
use Plaisio\Console\Style\PlaisioStyle;
use SetBased\Stratum\Middle\Exception\ResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  Abstract parent command for all PhpAutoDoc commands.
 */
abstract class PhpAutoDocCommand extends Command
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The data layer.
   *
   * @var DataLayer
   */
  protected DataLayer $dl;

  /**
   * The output decorator.
   *
   * @var PlaisioStyle
   */
  protected PlaisioStyle $io;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Initializes the output decorator and console IO object.
   *
   * @param InputInterface  $input  An InputInterface instance.
   * @param OutputInterface $output An OutputInterface instance.
   */
  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $this->io = new PlaisioStyle($input, $output);

    PhpAutoDoc::$io              = $this->io;
    PhpAutoDoc::$eventDispatcher = new AutoDocEventDispatcher(new CliKernel());

    ini_set('memory_limit', '-1');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Opens the SQLite database.
   *
   * @param string $path  The path to the SQLite database.
   * @param bool   $force If true forces a full build of the documentation.
   */
  protected function openDataLayer(string $path, bool $force = false): void
  {
    if ($force)
    {
      @unlink($path);
    }

    $ddlScript = trim(file_get_contents(__DIR__.'/../../lib/ddl/create_tables.sql'));
    $this->dl  = new DataLayer($path);
    $this->dl->executeNone('PRAGMA foreign_keys = ON');

    if (!$this->dl->padMiscTableExists('PAD_DDL'))
    {
      // Newly created database.
      $this->dl->executeNone($ddlScript);

      $this->dl->insertRow('PAD_DDL', ['ddl_version' => $this->getApplication()->getVersion(),
                                       'ddl_script'  => $ddlScript]);
    }
    else
    {
      // Existing database.
      try
      {
        $ddl = $this->dl->padDllGetDll();
      }
      catch (ResultException $exception)
      {
        $ddl = null;
      }

      if ($ddl===null || $ddl['ddl_script']!==$ddlScript || $ddl['ddl_version']!==$this->getApplication()->getVersion())
      {
        $this->openDataLayer($path, true);
      }
    }

    PhpAutoDoc::$dl = $this->dl;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
