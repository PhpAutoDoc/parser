<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\EventHandler;

use PhpAutoDoc\Parser\Parse\Event\NewSourceFileFoundEvent;
use PhpAutoDoc\Parser\Parse\FileParser;
use PhpAutoDoc\Parser\PhpAutoDoc;
use Plaisio\PlaisioInterface;

/**
 * Parses a source file.
 */
class ParseNewSourceFileFoundEventHandler
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Handles an event.
   *
   * @param PlaisioInterface        $object The parent Plaisio object.
   * @param NewSourceFileFoundEvent $event  The event.
   */
  public static function handle(PlaisioInterface $object, NewSourceFileFoundEvent $event): void
  {
    PhpAutoDoc::$io->text(sprintf('Parsing source file <fso>%s</fso>', $event->path()));

    $row = PhpAutoDoc::$dl->padFileGetByPath($event->path());

    $parser = new FileParser($row['fil_id']);
    $parser->parse();
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
