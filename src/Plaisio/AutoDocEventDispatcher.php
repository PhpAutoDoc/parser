<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Plaisio;

use PhpAutoDoc\Parser\Parse\Event\ClassFoundEvent;
use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\Event\SourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\Observer\ClassParserObserver;
use PhpAutoDoc\Parser\Parse\Observer\FileParserObserver;
use PhpAutoDoc\Parser\Parse\Observer\FileStoreObserver;
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
      ClassFoundEvent::class => [[[ClassParserObserver::class, 'handleClassFoundEvent'], null]],

      NewSourceFileFoundEvent::class => [[[FileParserObserver::class, 'handleNewSourceFileFoundEvent'], null]],

      SourceFileFoundEvent::class => [[[FileStoreObserver::class, 'handleSourceFileFoundEvent'], null]]
    ];

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
