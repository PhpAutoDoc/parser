<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\Event\ObsoleteSourceFileEvent;
use PhpAutoDoc\Parser\Parse\MainParser;

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
   * @param ObsoleteSourceFileEvent $event The event.
   */
  public static function handle(ObsoleteSourceFileEvent $event): void
  {
    PhpAutoDoc::$dl->padFileDeleteFile($event->filId());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
