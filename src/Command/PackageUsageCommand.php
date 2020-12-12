<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Command;

use PhpAutoDoc\Parser\PhpAutoDoc;
use SetBased\Helper\Cast;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List all projects files using a package.
 */
class PackageUsageCommand extends PhpAutoDocCommand
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function configure()
  {
    $this->setName('php-auto-doc:package-usage')
         ->setDescription('List all projects files using a package')
         ->addArgument('store', InputArgument::REQUIRED, 'The path to the SQLite database')
         ->addArgument('package', InputArgument::REQUIRED, 'The package name');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->openDataLayer(Cast::toManString($input->getArgument('store')));
    $package = Cast::toManString($input->getArgument('package'));

    $pckId = $this->fetchPckId($package);
    if ($pckId===null)
    {
      $this->io->error(sprintf('Package %s not found.', $package));

      return -1;
    }

    $usages = $this->fetchUsages($pckId);

    if (!empty($usages))
    {
      $this->io->title('Package Usage');
      $this->io->text(sprintf('%s:', $package));
      $this->io->text('');

      $this->io->listing($usages);
    }

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fetches the usages of a package.
   *
   * @param int $pckId the ID of the package.
   *
   * @return array
   */
  protected function fetchUsages(int $pckId): array
  {
    $usages = [];
    $rows   = PhpAutoDoc::$dl->padPackagesGetUsages($pckId);
    foreach ($rows as $row)
    {
      $usages[] = sprintf('%s:%s', $row['fil_path'], $row['use_line_start']);
    }

    return $usages;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the ID of a package.
   *
   * @param string $package The name of the package.
   *
   * @return int|null
   */
  private function fetchPckId(string $package): ?int
  {
    $parts = explode('/', $package);
    if (count($parts)!==2)
    {
      return null;
    }

    $row = PhpAutoDoc::$dl->padPackageSearch($parts[0], $parts[1]);

    return $row['pck_id'] ?? null;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
