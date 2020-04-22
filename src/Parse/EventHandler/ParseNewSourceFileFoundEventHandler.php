<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\FileParser;
use PhpAutoDoc\Parser\PhpAutoDoc;

/**
 * Parses a source file.
 */
class ParseNewSourceFileFoundEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param NewSourceFileFoundEvent $event The event.
   */
  public static function handle(NewSourceFileFoundEvent $event): void
  {
    PhpAutoDoc::$io->text(sprintf('Parsing source file <fso>%s</fso>', $event->path()));

    $row = PhpAutoDoc::$dl->padFileGetByPath($event->path());

    $parser = new FileParser($row['fil_id']);
    $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
