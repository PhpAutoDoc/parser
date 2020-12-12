<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Observer;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\FileParser;
use Plaisio\PlaisioInterface;

/**
 * Observer for parsing source files.
 */
class FileParserObserver
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a NewSourceFileFoundEvent event.
   *
   * @param PlaisioInterface        $object The parent Plaisio object.
   * @param NewSourceFileFoundEvent $event  The event.
   */
  public static function handleNewSourceFileFoundEvent(PlaisioInterface $object, NewSourceFileFoundEvent $event): void
  {
    $parser = new FileParser($event->getFilId());
    $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
