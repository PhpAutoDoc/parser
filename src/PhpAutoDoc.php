<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser;

use Plaisio\Console\Style\PlaisioStyle;
use Plaisio\Event\EventDispatcher;

/**
 *
 */
class PhpAutoDoc
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The DataLayer to the PhpAutoDoc SQLite database.
   *
   * @var DataLayer
   */
  public static $dl;

  /**
   * The event dispatcher.
   *
   * @var EventDispatcher
   */
  public static $eventDispatcher;

  /**
   * The output decorator.
   *
   * @var PlaisioStyle
   */
  public static $io;

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
