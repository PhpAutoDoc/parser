<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Observer;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\Event\SourceFileFoundEvent;
use PhpAutoDoc\Parser\PhpAutoDoc;
use Plaisio\PlaisioInterface;
use SetBased\Helper\Cast;

/**
 * Observer for maintaining files in the store.
 */
class FileStoreObserver
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a SourceFileFoundEvent event.
   *
   * @param PlaisioInterface     $object The parent Plaisio object.
   * @param SourceFileFoundEvent $event  The event.
   */
  public static function handleSourceFileFoundEvent(PlaisioInterface $object, SourceFileFoundEvent $event): void
  {
    $path   = $event->path();
    $source = file_get_contents($path);

    $row = PhpAutoDoc::$dl->padFileSearchByPath($path);
    if ($row===null)
    {
      self::newSource($path, $event->isProject(), $source);
    }
    else
    {
      if ($row['fil_contents']!==$source)
      {
        self::obsoleteSource($row['fil_id']);
        self::newSource($path, $event->isProject(), $source);
      }
      else
      {
        PhpAutoDoc::$dl->padFileUpdateIsSeen($row['fil_id']);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the ID of the package to which a source file belongs.
   *
   * @param string $path The path to the source file.
   *
   * @return int|null
   */
  private static function getPckId(string $path): ?int
  {
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    if (count($parts)>=4 && $parts[0]==='vendor')
    {
      $row = PhpAutoDoc::$dl->padPackageSearch($parts[1], $parts[2]);
      if ($row===null)
      {
        $pckId = PhpAutoDoc::$dl->padPackageInsertPackage($parts[1], $parts[2]);
      }
      else
      {
        $pckId = $row['pck_id'];
      }
    }
    else
    {
      $pckId = null;
    }

    return $pckId;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Triggers a NewSourceFileFoundEvent event.
   *
   * @param string $path      The path to the source file.
   * @param bool   $isProject True if and only if the source does belong to the project.
   * @param string $contents  The source code.
   */
  private static function newSource(string $path, bool $isProject, string $contents): void
  {
    if (trim($contents)!=='')
    {
      $pckId = self::getPckId($path);
      $filId = PhpAutoDoc::$dl->padFileInsertFile($pckId,
                                                  $path,
                                                  Cast::toManInt($isProject),
                                                  $contents);

      PhpAutoDoc::$eventDispatcher->notify(new NewSourceFileFoundEvent($filId));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Deletes an obsolete source file.
   *
   * @param int $filId The ID of the obsolete source file.
   */
  private static function obsoleteSource(int $filId): void
  {
    PhpAutoDoc::$dl->padFileDeleteFile($filId);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
