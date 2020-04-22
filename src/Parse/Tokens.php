<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse;

use SetBased\Exception\FallenException;
use SetBased\Exception\LogicException;

/**
 * Helper class for manipulating tokens.
 */
class Tokens
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * A map from key in $tokens to ordinal (starting from 0) of tokens in $tokes.
   *
   * @var array
   */
  private $keyOrdinalMap;

  /**
   * The start and last line of each token.
   *
   * @var array[]
   */
  private $lines;

  /**
   * A map from offset in $string of a token to the corresponding key in $tokens.
   *
   * @var array
   */
  private $offsetKeyMap;

  /**
   * A map from ordinal (starting from 0) to the key of tokens in $tokes.
   *
   * @var array
   */
  private $ordinalKeyMap;

  /**
   * The tokens as a string ignoring T_WHITESPACE and T_COMMENT tokens.
   *
   * @var string
   */
  private $string;

  /**
   * The tokens.
   *
   * @var array
   */
  private $tokens;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Tokens constructor.
   *
   * @param array|string|null $code  The code as:
   *                                 <ul>
   *                                 <li>array: The PHP code split as tokens.
   *                                 <li>string: PHP code as string.
   *                                 </ul>
   * @param array[]|null      $lines The map from token key to start and end line of the token.
   */
  public function __construct($code = null, ?array $lines = null)
  {
    switch (true)
    {
      case is_string($code);
        $this->tokens = token_get_all($code);
        break;

      case is_array($code):
        $this->tokens = $code;
        break;

      case $code===null:
        $this->tokens = [];
        break;

      default:
        throw new FallenException('code', gettype($code));
    }

    $this->stringify();
    $this->lineCount($lines);
    $this->initKeyOrdinalMaps();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the offset of the last token given the string representation of tokens.
   *
   * @param string $string
   *
   * @return int
   */
  public static function offsetLastToken(string $string): int
  {
    $pos = strrpos($string, ' ', -2);
    if ($pos===false)
    {
      return 0;
    }

    return $pos + 1;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all tokens between ( and ) or { and }.
   *
   * @param array  $tokens The PHP source code in PHP tokens.
   * @param string $open   The opening ( or }.
   * @param string $close  The closing ( or }.
   *
   * @return array
   */
  private static function findBlock(array $tokens, string $open, string $close): array
  {
    $level    = 0;
    $keyOpen  = null;
    $filtered = [];
    foreach ($tokens as $key => $token)
    {
      if ($keyOpen===null)
      {
        // We are looking for opening opening.
        if (is_string($token) && $token==$open)
        {
          $keyOpen = $key;
          $level++;
        }
      }
      else
      {
        // We are looking for closing.
        if (is_string($token) && $token==$open)
        {
          $level++;
        }
        else
        {
          if (is_string($token) && $token==$close)
          {
            $level--;

            if ($level===0)
            {
              $keyOpen = null;

              return $filtered;
            }
          }
        }

        $filtered[$key] = $token;
      }
    }

    return [];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes all tokens between ( and ) or { and }.
   *
   * @param array  $tokens The PHP source code in PHP tokens.
   * @param string $open   The opening ( or }.
   * @param string $close  The closing ( or }.
   *
   * @return array
   */
  private static function removeBlocks(array $tokens, string $open, string $close): array
  {
    $level    = 0;
    $keyOpen  = null;
    $filtered = [];
    foreach ($tokens as $key => $token)
    {
      if ($keyOpen===null)
      {
        // We are looking for opening opening.
        if (is_string($token) && $token==$open)
        {
          $keyOpen = $key;
          $level++;
        }

        $filtered[$key] = $token;
      }
      else
      {
        // We are looking for closing.
        if (is_string($token) && $token==$open)
        {
          $level++;
        }
        else
        {
          if (is_string($token) && $token==$close)
          {
            $level--;

            if ($level===0)
            {
              $keyOpen        = null;
              $filtered[$key] = $token;
            }
          }
        }
      }
    }

    return $filtered;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the tokens as a string.
   *
   * @return string
   */
  public function asString(): string
  {
    return $this->string;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the code of the all tokens between (and including) two tokens given the keys of two tokens.
   *
   * @param int $key1 The key of the first token.
   * @param int $key2 The key of the second token.
   *
   * @return string
   */
  public function code(int $key1, int $key2): string
  {
    $code = '';
    for ($key = $key1; $key<=$key2; $key++)
    {
      $token = $this->tokens[$key] ?? null;
      if (is_array($token))
      {
        $code .= $token[1];
      }
      elseif (is_string($token))
      {
        $code .= $token;
      }
    }

    return $code;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the body of the first parenthesized block as a Token object.
   *
   * @return Tokens
   */
  public function findFirstCurlyParenthesizedBlock(): Tokens
  {
    return new self(self::findBlock($this->tokens, '{', '}'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the body of the first breached block as a Token object.
   *
   * @return Tokens
   */
  public function findFirstParenthesizedBlock(): Tokens
  {
    return new self(self::findBlock($this->tokens, '(', ')'));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the key of the first token of a certain type.
   *
   * @param int|string $type The token. Either T_* constant or string.
   *
   * @return int
   */
  public function findFirstToken($type): int
  {
    $key = $this->searchFirstToken($type);

    if ($key===null)
    {
      throw new LogicException('Token %s not found', $type);
    }

    return $key;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the key of a token given its offset in the string representation of this tokens.
   *
   * @param int $offset
   *
   * @return int
   */
  public function keyByOffset(int $offset): int
  {
    return $this->offsetKeyMap[$offset];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the key of a token given its ordinal number in this tokens.
   *
   * @param int $ordinal The ordinal of the key.
   *
   * @return int
   */
  public function keyByOrdinal(int $ordinal): int
  {
    return $this->ordinalKeyMap[$ordinal];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the start end end line numbers of a token or of all tokens.
   *
   * @param int|null $key The key of the token or null for all tokens.
   *
   * @return array
   */
  public function lines(?int $key = null): array
  {
    if ($key!==null)
    {
      return $this->lines[$key];
    }

    $first = reset($this->ordinalKeyMap);
    $last  = end($this->ordinalKeyMap);

    return ['start' => $this->lines[$first]['start'],
            'end'   => $this->lines[$last]['end']];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the key of next token (ignoring T_COMMENT and T_WHITESPACE tokens).
   *
   * @param int $key The key of the current token.
   *
   * @return int
   */
  public function next(int $key): int
  {
    while (true)
    {
      $ordinal = $this->ordinalByKey($key);
      $key     = $this->keyByOrdinal($ordinal + 1);
      $token   = $this->tokens[$key];

      if (is_string($token) || (is_array($token) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])))
      {
        return $key;
      }
    }

    // Not reached.
    return -1;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the ordinal of a token given its key in this tokens.
   *
   * @param int $key The key.
   *
   * @return int
   */
  public function ordinalByKey(int $key): int
  {
    return $this->keyOrdinalMap[$key];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the key of the first token of a certain type. Returns null when the token is not found.
   *
   * @param int|string $type The token. Either T_* constant or string.
   *
   * @return int|null
   */
  public function searchFirstToken($type): ?int
  {
    foreach ($this->tokens as $key => $token)
    {
      if (is_int($type) && is_array($token) && $token[0]===$type)
      {
        return $key;
      }

      if (is_string($type) && is_string($token) && $token===$type)
      {
        return $key;
      }
    }

    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a slice of tokens between (and including) two tokens given the keys of two tokens.
   *
   * @param int $key1 The key of the first token.
   * @param int $key2 The key of the second token.
   *
   * @return Tokens
   */
  public function slice(int $key1, int $key2): Tokens
  {
    $tokens = [];
    foreach ($this->tokens as $key => $token)
    {
      if ($key1<=$key and $key<=$key2)
      {
        $tokens[$key] = $token;
      }
    }

    return new Tokens($tokens, $this->lines);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a Token object same as this Token object but with all code between () and {} removed.
   *
   * @return Tokens
   */
  public function withoutBlocks(): Tokens
  {
    $tokens = self::removeBlocks($this->tokens, '{', '}');
    $tokens = self::removeBlocks($tokens, '(', ')');

    return new Tokens($tokens, $this->lines);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a Token object same as this Token object but with all code between {} removed.
   *
   * @return Tokens
   */
  public function withoutCurlyParenthesizedBlocks(): Tokens
  {
    $tokens = self::removeBlocks($this->tokens, '{', '}');

    return new Tokens($tokens, $this->lines);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Initializes $keyOrdinalMap and $ordinalKeyMap.
   */
  private function initKeyOrdinalMaps(): void
  {
    $this->ordinalKeyMap = array_keys($this->tokens);
    $this->keyOrdinalMap = array_flip($this->ordinalKeyMap);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Initializes the start and last line of each token.
   *
   * @param array[]|null $lines The map from token key to start and end line of the token.
   */
  private function lineCount(?array $lines): void
  {
    if ($lines===null)
    {
      $this->lineCountNew();
    }
    else
    {
      $this->lineCountCopy($lines);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Copies the original line count.
   *
   * @param array[] $lines The map from token key to start and end line of the token.
   */
  private function lineCountCopy(array $lines): void
  {
    foreach ($this->tokens as $key => $token)
    {
      $this->lines[$key] = $lines[$key];
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Initializes the start and last line of each token.
   */
  private function lineCountNew(): void
  {
    $line = 1;
    foreach ($this->tokens as $key => $token)
    {
      switch (true)
      {
        case is_array($token):
          $start             = $token[2];
          $last              = $token[2] + mb_substr_count($token[1], PHP_EOL);
          $this->lines[$key] = ['start' => $start,
                                'end'   => $last];
          $line              = $last;
          break;

        case is_string($token):
          $this->lines[$key] = ['start' => $line,
                                'end'   => $line];
          break;

        default:
          throw new FallenException('type', gettype($token));
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Converts PHP tokens to a string ignoring T_WHITESPACE and T_COMMENT tokens.
   */
  private function stringify(): void
  {
    $this->string       = '';
    $this->offsetKeyMap = [];
    foreach ($this->tokens as $key => $token)
    {
      if (is_array($token))
      {
        if (!in_array($token[0], [T_WHITESPACE, T_COMMENT]))
        {
          // We must use strlen (which return the length in bytes) and not mb_strlen because PCRE option
          // PREG_OFFSET_CAPTURE will return the offset in bytes too.
          $this->offsetKeyMap[strlen($this->string)] = $key;
          $this->string                              .= token_name($token[0]).' ';
        }
      }
      else
      {
        $this->offsetKeyMap[strlen($this->string)] = $key;
        $this->string                              .= $token.' ';
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
