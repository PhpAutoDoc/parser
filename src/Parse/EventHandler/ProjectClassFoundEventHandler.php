<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\ClassParser;
use PhpAutoDoc\Parser\Parse\Event\ProjectClassFoundEvent;
use Plaisio\PlaisioInterface;

/**
 * The handler for a ProjectClassFoundEvent event.
 */
class ProjectClassFoundEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param PlaisioInterface       $object The parent Plaisio object.
   * @param ProjectClassFoundEvent $event  The event.
   */
  public static function handle(PlaisioInterface $object, ProjectClassFoundEvent $event): void
  {
    $parser = new ClassParser($event->clsId());
    $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
