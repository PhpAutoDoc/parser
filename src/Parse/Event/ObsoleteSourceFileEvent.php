<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Event;

use PhpAutoDoc\Parser\PhpAutoDoc;

/**
 * Event triggered when an obsolete source file has been found.
 */
class ObsoleteSourceFileEvent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The ID of the obsolete source file.
   *
   * @var int
   */
  private $filId;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int $filId The ID of the obsolete source file.
   */
  public function __construct(int $filId)
  {
    $this->filId = $filId;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Return the ID of the obsolete source file.
   *
   * @return int
   */
  public function filId(): int
  {
    return $this->filId;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
