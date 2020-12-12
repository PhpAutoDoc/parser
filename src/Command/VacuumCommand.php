<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Command;

use SetBased\Helper\Cast;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes obsolete data from the SQLite database.
 */
class VacuumCommand extends PhpAutoDocCommand
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function configure()
  {
    $this->setName('php-auto-doc:vacuum')
         ->setDescription('Removes obsolete data from the SQLite database')
         ->addArgument('store', InputArgument::REQUIRED, 'The path to the SQLite database');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->io->title('Vacuum');

    $store = Cast::toManString($input->getArgument('store'));

    $size1 = filesize($store);
    $this->io->text(sprintf('Initial size: %s', Helper::formatMemory($size1)));

    $this->openDataLayer(Cast::toManString($input->getArgument('store')));
    $this->dl->executeNone('pragma foreign_keys = on');
    $this->dl->padVacuumDeleteTokens();
    $this->dl->padVacuumDocblock();
    $this->dl->padVacuumVacuum();
    $this->dl->close();

    clearstatcache();
    $size2 = filesize($store);
    $this->io->text(sprintf('Final size:   %s', Helper::formatMemory($size2)));
    $this->io->text(sprintf('Saved:        %s', Helper::formatMemory($size1 - $size2)));

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
