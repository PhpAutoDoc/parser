<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Plaisio;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\Event\ObsoleteSourceFileEvent;
use PhpAutoDoc\Parser\Parse\Event\ProjectClassFoundEvent;
use PhpAutoDoc\Parser\Parse\Event\SourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\EventHandler\NewSourceFileFoundEventHandler;
use PhpAutoDoc\Parser\Parse\EventHandler\ObsoleteSourceFileEventHandler;
use PhpAutoDoc\Parser\Parse\EventHandler\ParseNewSourceFileFoundEventHandler;
use PhpAutoDoc\Parser\Parse\EventHandler\ProjectClassFoundEventHandler;
use PhpAutoDoc\Parser\Parse\EventHandler\SourceFileFoundEventHandler;
use Plaisio\Event\CoreEventDispatcher;

/**
 * Concrete implementation of the event dispatcher.
 */
class AutoDocEventDispatcher extends CoreEventDispatcher
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected static $notifyHandlers =
    [
      NewSourceFileFoundEvent::class => [[[NewSourceFileFoundEventHandler::class, 'handle'], null],
                                         [[ParseNewSourceFileFoundEventHandler::class, 'handle'], null]],

      ObsoleteSourceFileEvent::class => [[[ObsoleteSourceFileEventHandler::class, 'handle'], null]],

      ProjectClassFoundEvent::class => [[[ProjectClassFoundEventHandler::class, 'handle'], null]],

      SourceFileFoundEvent::class => [[[SourceFileFoundEventHandler::class, 'handle'], null]]
    ];

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
