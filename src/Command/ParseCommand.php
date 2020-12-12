<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Command;

use PhpAutoDoc\Parser\Parse\MainParser;
use SetBased\Helper\Cast;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Parses given sources and populates the SQLite database.
 */
class ParseCommand extends PhpAutoDocCommand
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function configure()
  {
    $this->setName('php-auto-doc:parse')
         ->setDescription('Parses given sources and populates the SQLite database')
         ->addArgument('store',
                       InputArgument::REQUIRED,
                       'The path to the SQLite database')
         ->addArgument('external-sources',
                       InputArgument::REQUIRED,
                       'Comma-separated list of files to parse. The wildcards ? and * are supported')
         ->addArgument('project-sources',
                       InputArgument::REQUIRED,
                       'Comma-separated list of files to parse. The wildcards ? and * are supported')
         ->addOption('force',
                     null,
                     InputOption::VALUE_NONE,
                     'Forces a full build of the documentation, does not increment existing documentation');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->io->title('Parse');

    $this->openDataLayer(Cast::toManString($input->getArgument('store')));

    $parser = new MainParser($input->getArgument('external-sources'),
                             $input->getArgument('project-sources'),
                             $input->getOptions());

    return $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
