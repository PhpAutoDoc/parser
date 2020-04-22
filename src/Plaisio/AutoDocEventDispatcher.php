<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Plaisio;

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
      'PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent' => [['PhpAutoDoc\Parser\Parse\EventHandler\NewSourceFileFoundEventHandler::handle',
                                                                   null],
                                                                  ['PhpAutoDoc\Parser\Parse\EventHandler\ParseNewSourceFileFoundEventHandler::handle',
                                                                   null]],

      'PhpAutoDoc\Parser\Parse\Event\ObsoleteSourceFileEvent' => [['PhpAutoDoc\Parser\Parse\EventHandler\ObsoleteSourceFileEventHandler::handle',
                                                                   null]],

      'PhpAutoDoc\Parser\Parse\Event\ProjectClassFoundEvent' => [['PhpAutoDoc\Parser\Parse\EventHandler\ProjectClassFoundEventHandler::handle',
                                                                  null]],

      'PhpAutoDoc\Parser\Parse\Event\SourceFileFoundEvent' => [['PhpAutoDoc\Parser\Parse\EventHandler\SourceFileFoundEventHandler::handle',
                                                                null]]
    ];

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
