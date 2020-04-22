<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\ClassParser;
use PhpAutoDoc\Parser\Parse\Event\ProjectClassFoundEvent;

/**
 * The handler for a ProjectClassFoundEvent event.
 */
class ProjectClassFoundEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param ProjectClassFoundEvent $event The event.
   */
  public static function handle(ProjectClassFoundEvent $event): void
  {
    $parser = new ClassParser($event->clsId());
    $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
