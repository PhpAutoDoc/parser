<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Event;

/**
 * Event triggered when (the main parser) has found a PHP source file.
 */
class NewSourceFileFoundEvent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The ID of the source file.
   *
   * @var int
   */
  private int $filId;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int $filId The ID of the source file.
   */
  public function __construct(int $filId)
  {
    $this->filId = $filId;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Return the ID of the source file.
   *
   * @return int
   */
  public function getFilId(): int
  {
    return $this->filId;
  }
}

//----------------------------------------------------------------------------------------------------------------------
