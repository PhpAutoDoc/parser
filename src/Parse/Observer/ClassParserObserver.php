<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Observer;

use PhpAutoDoc\Parser\Parse\ClassParser;
use PhpAutoDoc\Parser\Parse\Event\ClassFoundEvent;
use Plaisio\PlaisioInterface;

/**
 * Observer for parsing classes.
 */
class ClassParserObserver
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles a ClassFoundEvent event.
   *
   * @param PlaisioInterface $object The parent Plaisio object.
   * @param ClassFoundEvent  $event  The event.
   */
  public static function handleClassFoundEvent(PlaisioInterface $object, ClassFoundEvent $event): void
  {
    $parser = new ClassParser($event->clsId());
    $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
