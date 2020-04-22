<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Helper;

/**
 * Utility class for namespaces.
 *
 * @see https://www.php.net/manual/en/language.namespaces.basics.php
 */
class NamespaceHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the fully qualified name of an element.
   *
   * @param string|null $namespace The current namespace.
   * @param string      $name      The name of the element.
   *
   * @return string
   */
  public static function fullyQualifiedName(?string $namespace, string $name): string
  {
    if (mb_substr($name, 0, 1)=='\\')
    {
      // Name is a fully qualified name.
      return ltrim($name, '\\');
    }

    if ($namespace===null)
    {
      // Global namespace. So, name is fully qualified name.
      return $name;
    }

    return $namespace.'\\'.$name;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Splist a fully qualified name into an array with 3 elements namespace, name, and fully qualified name.
   *
   * @param string $name The fully qualified name.
   *
   * @return array
   */
  public static function split(string $name): array
  {
    $fullName  = ltrim($name, '\\');
    $parts     = explode('\\', $name);
    $name      = array_pop($parts);
    $namespace = (empty($parts)) ? null : implode('\\', $parts);

    return [$namespace, $name, $fullName];
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
