<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\Event\ObsoleteSourceFileEvent;
use PhpAutoDoc\Parser\Parse\Event\SourceFileFoundEvent;
use PhpAutoDoc\Parser\PhpAutoDoc;
use Plaisio\PlaisioInterface;

/**
 * The main handler for a SourceFileFoundEvent event.
 */
class SourceFileFoundEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param PlaisioInterface     $object The parent Plaisio object.
   * @param SourceFileFoundEvent $event  The event.
   */
  public static function handle(PlaisioInterface $object, SourceFileFoundEvent $event): void
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
        PhpAutoDoc::$dl->padFileUpdateIsParsed($row['fil_id']);
      }
    }
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
      PhpAutoDoc::$eventDispatcher->notify(new NewSourceFileFoundEvent($path, $isProject, $contents));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Triggers a ObsoleteSourceFileEvent event.
   *
   * @param int $filId The ID of the obsolete source file.
   */
  private static function obsoleteSource(int $filId): void
  {
    PhpAutoDoc::$eventDispatcher->notify(new ObsoleteSourceFileEvent($filId));
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
