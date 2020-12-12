<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Command;

use PhpAutoDoc\Parser\PhpAutoDoc;
use SetBased\Helper\Cast;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List all implicit packages.
 */
class PackageImplicitCommand extends PhpAutoDocCommand
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function configure()
  {
    $this->setName('php-auto-doc:package-implicit')
         ->setDescription('List all implicit packages')
         ->addArgument('store', InputArgument::REQUIRED, 'The path to the SQLite database');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->openDataLayer(Cast::toManString($input->getArgument('store')));

    $packagesUsed     = $this->fetchUsedPackages();
    $packagesRequired = $this->fetchRequiredPackages();
    $packagesMissing  = array_diff($packagesUsed, $packagesRequired);

    if (!empty($packagesMissing))
    {
      $this->io->title('Implicit Packages');

      $this->io->listing($packagesMissing);
    }

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fetches the requires packages from composer.json.
   */
  private function fetchRequiredPackages(): array
  {
    $data = json_decode(file_get_contents('composer.json'), true);

    $packages = [];
    foreach ($data['require'] ?? [] as $package => $dummy)
    {
      if (str_contains($package, '/'))
      {
        $packages[] = $package;
      }
    }

    foreach ($data['require-dev'] ?? [] as $package => $dummy)
    {
      if (str_contains($package, '/'))
      {
        $packages[] = $package;
      }
    }

    return $packages;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Fetches used packages found in code.
   *
   * @return array
   */
  private function fetchUsedPackages(): array
  {
    $packages = [];
    $rows     = PhpAutoDoc::$dl->padPackagesGetAllUsedInCode();
    foreach ($rows as $row)
    {
      $packages[] = $row['pck_vendor_name'].'/'.$row['pck_project_name'];
    }

    return $packages;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
