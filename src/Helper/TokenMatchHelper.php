<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Helper;

use PhpAutoDoc\Parser\Parse\Tokens;

/**
 * Utility class for extracting code using a match from preg_match or preg_match_all.
 *
 * Note: preg_match or preg_match_all must be called with options PREG_OFFSET_CAPTURE | PREG_SET_ORDER.
 */
class TokenMatchHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the code of a named subpattern.
   *
   * @param string      $name    The name of the subpattern.
   * @param array       $match   A match from preg_match() or preg_match_all().
   * @param Tokens      $tokens1 The tokens on which the regular expression was executed.
   * @param Tokens|null $tokens2 The original tokens. If null $tokens1 will be used.
   *
   * @return string|null
   */
  public static function code(string $name, array $match, Tokens $tokens1, Tokens $tokens2 = null): ?string
  {
    $offset = $match[$name][1] ?? -1;
    if ($offset==-1)
    {
      return null;
    }
    $offset1 = $match[$name][1];
    $offset2 = $offset1 + Tokens::offsetLastToken($match[$name][0]);
    $key1    = $tokens1->keyByOffset($offset1);
    $key2    = $tokens1->keyByOffset($offset2);

    return ($tokens2 ?? $tokens1)->code($key1, $key2);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the tokens of match code block.
   *
   * @param array  $match   A match from preg_match() or preg_match_all().
   * @param Tokens $tokens1 The (reduced) tokens on which the regular expression was executed.
   * @param Tokens $tokens2 The original tokens.
   *
   * @return Tokens
   */
  public static function codeBlock(array $match, Tokens $tokens1, Tokens $tokens2): Tokens
  {
    $offset1 = $match[0][1];
    $offset2 = $offset1 + $tokens1::offsetLastToken($match[0][0]);
    $offset1 += ((isset($match['docblock'][1])) ? strlen($match['docblock'][0]) : 0);
    $key1    = $tokens1->keyByOffset($offset1);
    $key2    = $tokens1->keyByOffset($offset2);

    return $tokens2->slice($key1, $key2);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the details of a docblock found by preg_match() or preg_match_all().
   *
   * @param array  $match  A match from preg_match() or preg_match_all().
   * @param Tokens $tokens The tokens on which the regular expression was executed.
   *
   * @return array|null
   */
  public static function docblockDetails(array $match, Tokens $tokens): ?array
  {
    $offset = $match['docblock'][1] ?? -1;
    if ($offset==-1)
    {
      $docblock = null;
    }
    else
    {
      $key      = $tokens->keyByOffset($offset);
      $lines    = $tokens->lines($key);
      $docblock = ['doc_line_start' => $lines['start'],
                   'doc_line_end'   => $lines['end'],
                   'doc_docblock'   => $tokens->code($key, $key)];
    }

    return $docblock;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if an item is abstract. Otherwise returns false.
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return bool
   */
  public static function isAbstract(array $match, string $name = 'qualifiers'): bool
  {
    $qualifiers = $match[$name][0] ?? '';

    return (strpos($qualifiers, 'T_ABSTRACT ')!==false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if an item is a class. Otherwise returns false.
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return bool
   */
  public static function isClass(array $match, string $name = 'type'): bool
  {
    $type = $match[$name][0] ?? '';

    return (strpos($type, 'T_CLASS ')!==false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if an item is final. Otherwise returns false.
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return bool
   */
  public static function isFinal(array $match, string $name = 'qualifiers'): bool
  {
    $qualifiers = $match[$name][0] ?? '';

    return (strpos($qualifiers, 'T_FINAL ')!==false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if an item is an interface. Otherwise returns false.
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return bool
   */
  public static function isInterface(array $match, string $name = 'type'): bool
  {
    $type = $match[$name][0] ?? '';

    return (strpos($type, 'T_INTERFACE ')!==false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if an item is static. Otherwise returns false.
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return bool
   */
  public static function isStatic(array $match, string $name = 'qualifiers'): bool
  {
    $qualifiers = $match[$name][0] ?? '';

    return (strpos($qualifiers, 'T_STATIC ')!==false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if an item is a trait. Otherwise returns false.
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return bool
   */
  public static function isTrait(array $match, string $name = 'type'): bool
  {
    $type = $match[$name][0] ?? '';

    return (strpos($type, 'T_TRAIT ')!==false);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the visibility of an item
   *
   * @param array  $match A match from preg_match() or preg_match_all().
   * @param string $name  The name of the subpattern of the qualifiers.
   *
   * @return string
   */
  public static function visibility(array $match, string $name = 'qualifiers'): string
  {
    $qualifiers = $match[$name][0] ?? '';

    if (strpos($qualifiers, 'T_PRIVATE ')!==false)
    {
      return 'private';
    }

    if (strpos($qualifiers, 'T_PROTECTED ')!==false)
    {
      return 'protected';
    }

    // Either the item is declared public explicitly or is public by default.
    return 'public';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
