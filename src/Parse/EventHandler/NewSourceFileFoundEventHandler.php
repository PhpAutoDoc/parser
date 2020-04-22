<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\PhpAutoDoc;
use SetBased\Helper\Cast;

/**
 * The main handler for a NewSourceFileFoundEvent event.
 *
 * This handler inserts a row into PAD_FILE. All other handlers must come after this handler.
 */
class NewSourceFileFoundEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param NewSourceFileFoundEvent $event The event.
   */
  public static function handle(NewSourceFileFoundEvent $event): void
  {
    PhpAutoDoc::$dl->padFileInsertFile($event->path(),
                                       Cast::toManInt($event->isProject()),
                                       $event->contents());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
