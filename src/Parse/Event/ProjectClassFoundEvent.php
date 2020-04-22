<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Event;

/**
 * Event triggered when a class|interface|trait that belongs to the project to be documented has been found.
 */
class ProjectClassFoundEvent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The ID of the class.
   *
   * @var int
   */
  private $clsId;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int $clsId The ID of the class.
   */
  public function __construct(int $clsId)
  {
    $this->clsId = $clsId;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the ID of the class.
   *
   * @return int
   */
  public function clsId(): int
  {
    return $this->clsId;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
