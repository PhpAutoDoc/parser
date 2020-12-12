<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\Event\ObsoleteSourceFileEvent;
use PhpAutoDoc\Parser\PhpAutoDoc;
use Plaisio\PlaisioInterface;

/**
 * The main handler for a ObsoleteSourceFileEvent event.
 *
 * This handler delete a row from PAD_FILE. All other handlers must come before this handler.
 */
class ObsoleteSourceFileEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param PlaisioInterface        $object The parent Plaisio object.
   * @param ObsoleteSourceFileEvent $event  The event.
   */
  public static function handle(PlaisioInterface $object, ObsoleteSourceFileEvent $event): void
  {
    PhpAutoDoc::$dl->padFileDeleteFile($event->filId());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
