<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse\Event;

/**
 * Event triggered when (the main parser) has found a PHP source file.
 */
class SourceFileFoundEvent
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * True if and only if the source does belong to the project.
   *
   * @var bool
   */
  private bool $isProject;

  /**
   * The path to the source file.
   *
   * @var string
   */
  private string $path;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $path      The path to the source file.
   * @param bool   $isProject True if and only if the source does belong to the project.
   */
  public function __construct(string $path, bool $isProject)
  {
    $this->path      = $path;
    $this->isProject = $isProject;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if and only if the source does belong to the project.
   *
   * @return bool
   */
  public function isProject(): bool
  {
    return $this->isProject;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the path to the source file.
   *
   * @return string
   */
  public function path(): string
  {
    return $this->path;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
