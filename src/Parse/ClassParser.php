<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse;

use PhpAutoDoc\Parser\Helper\NamespaceHelper;
use PhpAutoDoc\Parser\Helper\TokenMatchHelper;
use PhpAutoDoc\Parser\PhpAutoDoc;
use SetBased\Helper\Cast;

/**
 *  Parses a single class|interface|trait.
 */
class ClassParser
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The ID of the class|interface|trait.
   *
   * @var int
   */
  private $clsId;

  /**
   * The basic details of a class.
   *
   * @var array
   */
  private $details;

  /**
   * The tokens of the PHP code of the class.
   *
   * @var Tokens
   */
  private $tokens;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int $clsId The ID of the class|interface|trait.
   */
  public function __construct(int $clsId)
  {
    $this->clsId   = $clsId;
    $this->details = PhpAutoDoc::$dl->padClassGetBasicDetails($this->clsId);
    $this->tokens  = unserialize($this->details['cls_tokens']);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the class.
   */
  public function parse(): void
  {
    PhpAutoDoc::$io->text(sprintf('Parsing class <fso>%s</fso>', $this->details['cls_fully_qualified_name']));

    $this->parseQualifiers();
    $this->parseParents();
    $this->parseConstants();
    $this->parseProperties();
    $this->parseMethods();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the constants from the class|interface|trait.
   *
   * @return array[] The details of the properties.
   */
  private function extractConstants(): array
  {
    $tokens = $this->tokens->findFirstCurlyParenthesizedBlock();
    $tokens = $tokens->withoutBlocks();

    preg_match_all('/(?<docblock>T_DOC_COMMENT )?(?<qualifiers>(T_PUBLIC |T_PROTECTED |T_PRIVATE )*)T_CONST (?<name>T_STRING )(= (?<value>(?:(?!; ).)+))?; /',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    $constants = [];
    foreach ($matches as $match)
    {
      $constantTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
      $lines          = $constantTokens->lines();

      $constants[] = ['docblock'   => TokenMatchHelper::docblockDetails($match, $tokens),
                      'visibility' => TokenMatchHelper::visibility($match),
                      'name'       => TokenMatchHelper::code('name', $match, $tokens),
                      'value'      => TokenMatchHelper::code('value', $match, $tokens),
                      'start'      => $lines['start'],
                      'end'        => $lines['end'],
                      'tokens'     => $constantTokens];
    }

    return $constants;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the arguments of a method.
   *
   * @param Tokens $tokens The tokens of the method.
   *
   * @return array[]
   */
  private function extractMethodArguments(Tokens $tokens): array
  {
    $tokens = $tokens->findFirstParenthesizedBlock();
    $tokens = $tokens->withoutBlocks();

    preg_match_all('/(?<type>T_STRING )?(?<name>T_VARIABLE )(= (?<default>(?:(?!, ).)+))?(, )?/',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    $arguments = [];
    foreach ($matches as $match)
    {
      $arguments[] = ['type'    => TokenMatchHelper::code('type', $match, $tokens),
                      'name'    => TokenMatchHelper::code('name', $match, $tokens),
                      'default' => TokenMatchHelper::code('default', $match, $tokens)];
    }

    return $arguments;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts all methods of the class|interface|trait.
   *
   * @return array[]
   */
  private function extractMethods(): array
  {
    $methods = [];

    $tokens = $this->tokens->findFirstCurlyParenthesizedBlock();
    $tokens = $tokens->withoutBlocks();

    preg_match_all('/(?<docblock>T_DOC_COMMENT )?(?<qualifiers>(T_FINAL |T_ABSTRACT |T_PUBLIC |T_PROTECTED |T_PRIVATE |T_STATIC ))*(T_FUNCTION )(?<name>T_STRING )\( \) (: (?<return>T_[A-Z0-9]* ))?({ } |; )/',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches as $match)
    {
      $methodTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
      $lines        = $methodTokens->lines();

      $methods[] = ['docblock'    => TokenMatchHelper::docblockDetails($match, $tokens),
                    'name'        => TokenMatchHelper::code('name', $match, $tokens),
                    'is_abstract' => TokenMatchHelper::isAbstract($match),
                    'is_final'    => TokenMatchHelper::isFinal($match),
                    'is_static'   => TokenMatchHelper::isStatic($match),
                    'visibility'  => TokenMatchHelper::visibility($match),
                    'return'      => TokenMatchHelper::code('return', $match, $tokens),
                    'start'       => $lines['start'],
                    'end'         => $lines['end'],
                    'tokens'      => $methodTokens];
    }

    return $methods;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the parent class|interface|trait.
   *
   * @param Tokens $tokens The tokens before the body of the class|interface|trait.
   *
   * @return array|null
   */
  private function extractParentsExtends(Tokens $tokens): ?array
  {
    $n = preg_match('/(T_EXTENDS )(?<parent>(T_NS_SEPARATOR |T_STRING )+)/',
                    $tokens->asString(),
                    $matches,
                    PREG_OFFSET_CAPTURE);

    if ($n==1)
    {
      $name          = TokenMatchHelper::code('parent', $matches, $tokens);
      $extendsTokens = TokenMatchHelper::codeBlock($matches, $tokens, $this->tokens);
      $lines         = $extendsTokens->lines();

      return ['name'      => $name,
              'full_name' => NamespaceHelper::fullyQualifiedName($this->details['cls_namespace'], $name),
              'start'     => $lines['start'],
              'end'       => $lines['end'],
              'tokens'    => $extendsTokens];
    }

    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the implemented interfaces.
   *
   * @param Tokens $tokens The tokens before the body of the class|interface|trait.
   *
   * @return array[]
   */
  private function extractParentsInterfaces(Tokens $tokens): array
  {
    $interfaces = [];

    preg_match('/(?<implements>T_IMPLEMENTS (((T_NS_SEPARATOR |T_STRING )+)(, )?)*)/',
               $tokens->asString(),
               $matches1,
               PREG_OFFSET_CAPTURE);

    $implements = $matches1['implements'][0] ?? '';
    if ($implements!=='')
    {
      $tokens = TokenMatchHelper::codeBlock($matches1, $this->tokens, $this->tokens);

      preg_match_all('/(?<interface>(T_NS_SEPARATOR |T_STRING )+)/',
                     $tokens->asString(),
                     $matches2,
                     PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

      foreach ($matches2 as $match2)
      {
        $name            = TokenMatchHelper::code('interface', $match2, $tokens);
        $interfaceTokens = TokenMatchHelper::codeBlock($match2, $tokens, $this->tokens);
        $lines           = $interfaceTokens->lines();

        $interfaces[] = ['name'      => $name,
                         'full_name' => NamespaceHelper::fullyQualifiedName($this->details['cls_namespace'], $name),
                         'start'     => $lines['start'],
                         'end'       => $lines['end'],
                         'tokens'    => $interfaceTokens];
      }
    }

    return $interfaces;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts used traits.
   */
  private function extractParentsTraits(): array
  {
    $traits = [];

    preg_match_all('/T_USE (?<traits>(((T_NS_SEPARATOR |T_STRING )+)(, )?)+)/',
                   $this->tokens->asString(),
                   $matches1,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches1 as $match1)
    {
      $tokens = TokenMatchHelper::codeBlock($match1, $this->tokens, $this->tokens);

      preg_match_all('/(?<trait>(T_NS_SEPARATOR |T_STRING )+)/',
                     $tokens->asString(),
                     $matches2,
                     PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

      foreach ($matches2 as $match2)
      {
        $name        = TokenMatchHelper::code('trait', $match2, $tokens);
        $traitTokens = TokenMatchHelper::codeBlock($match2, $tokens, $this->tokens);
        $lines       = $traitTokens->lines();

        $traits[] = ['name'      => $name,
                     'full_name' => NamespaceHelper::fullyQualifiedName($this->details['cls_namespace'], $name),
                     'start'     => $lines['start'],
                     'end'       => $lines['end'],
                     'tokens'    => $traitTokens];
      }
    }

    return $traits;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the properties from the class|interface|trait.
   *
   * @return array[] The details of the properties.
   */
  private function extractProperties(): array
  {
    $tokens = $this->tokens->findFirstCurlyParenthesizedBlock();
    $tokens = $tokens->withoutBlocks();

    preg_match_all('/(?<docblock>T_DOC_COMMENT )?(?<qualifiers>(T_VAR |T_PUBLIC |T_PROTECTED |T_PRIVATE |T_STATIC ))+(?<type>T_STRING )?(?<name>T_VARIABLE )(= (?<value>(?:(?!; ).)+))?; /',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    $properties = [];
    foreach ($matches as $match)
    {
      $propertyTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
      $lines          = $propertyTokens->lines();

      $properties[] = ['docblock'   => TokenMatchHelper::docblockDetails($match, $tokens),
                       'type'       => TokenMatchHelper::code('type', $match, $tokens),
                       'visibility' => TokenMatchHelper::visibility($match),
                       'is_static'  => TokenMatchHelper::isStatic($match),
                       'name'       => TokenMatchHelper::code('name', $match, $tokens),
                       'value'      => TokenMatchHelper::code('value', $match, $tokens),
                       'start'      => $lines['start'],
                       'end'        => $lines['end'],
                       'tokens'     => $propertyTokens];
    }

    return $properties;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parsers all properties of the class.
   */
  private function parseConstants(): void
  {
    $constants = $this->extractConstants();
    foreach ($constants as $constant)
    {
      if ($constant['docblock']===null)
      {
        $docId = null;
      }
      else
      {
        $docId = PhpAutoDoc::$dl->padDocblockInsertDocblock($constant['docblock']['doc_line_start'],
                                                                    $constant['docblock']['doc_line_end'],
                                                                    $constant['docblock']['doc_docblock']);
      }

      PhpAutoDoc::$dl->padClassInsertConstant($this->clsId,
                                                      $docId,
                                                      $constant['name'],
                                                      $constant['visibility'],
                                                      $constant['value'],
                                                      $constant['start'],
                                                      $constant['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parsers the arguments of a method.
   *
   * @param int    $mthId  The ID of the method.
   * @param Tokens $tokens The tokens of the method.
   */
  private function parseMethodArguments(int $mthId, Tokens $tokens): void
  {
    $arguments = $this->extractMethodArguments($tokens);

    foreach ($arguments as $i => $argument)
    {
      PhpAutoDoc::$dl->padClassInsertMethodArgument($mthId,
                                                            $i + 1,
                                                            $argument['type'],
                                                            $argument['name'],
                                                            $argument['default']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the methods of a class.
   */
  private function parseMethods(): void
  {
    $methods = $this->extractMethods();

    foreach ($methods as $method)
    {
      if ($method['docblock']===null)
      {
        $docId = null;
      }
      else
      {
        $docId = PhpAutoDoc::$dl->padDocblockInsertDocblock($method['docblock']['doc_line_start'],
                                                                    $method['docblock']['doc_line_end'],
                                                                    $method['docblock']['doc_docblock']);
      }

      $mthId = PhpAutoDoc::$dl->padClassInsertMethod($this->clsId,
                                                             $docId,
                                                             $method['name'],
                                                             Cast::toManInt($method['is_abstract']),
                                                             Cast::toManInt(mb_strtolower($method['name'])=='__construct'),
                                                             Cast::toManInt(mb_strtolower($method['name'])=='__destruct'),
                                                             Cast::toManInt($method['is_final']),
                                                             Cast::toManInt($method['is_static']),
                                                             $method['visibility'],
                                                             $method['start'],
                                                             $method['end']);

      $this->parseMethodArguments($mthId, $method['tokens']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extract the parent classes, implemented interfaces and used traits.
   */
  private function parseParents(): void
  {
    $key1   = $this->tokens->keyByOrdinal(0);
    $key2   = $this->tokens->findFirstToken('{');
    $tokens = $this->tokens->slice($key1, $key2);

    $weight = 0;
    $this->parseParentsExtends($tokens, $weight);
    $this->parseParentsInterfaces($tokens, $weight);
    $this->parseParentsTraits($weight);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the parent class.
   *
   * @param Tokens $tokens The tokens before the body of the class.
   * @param int    $weight The weight of the previous parent.
   */
  private function parseParentsExtends(Tokens $tokens, int &$weight): void
  {
    $parent = $this->extractParentsExtends($tokens);

    if ($parent!==null)
    {
      PhpAutoDoc::$dl->padClassInsertParent($this->clsId,
                                                    $parent['name'],
                                                    $parent['full_name'],
                                                    ++$weight,
                                                    1,
                                                    0,
                                                    0,
                                                    $parent['start'],
                                                    $parent['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the implemented interfaces.
   *
   * @param Tokens $tokens The tokens before the body of the class.
   * @param int    $weight The weight of the previous parent.
   */
  private function parseParentsInterfaces(Tokens $tokens, int &$weight): void
  {
    $interfaces = $this->extractParentsInterfaces($tokens);

    foreach ($interfaces as $interface)
    {
      PhpAutoDoc::$dl->padClassInsertParent($this->clsId,
                                                    $interface['name'],
                                                    $interface['full_name'],
                                                    ++$weight,
                                                    0,
                                                    1,
                                                    0,
                                                    $interface['start'],
                                                    $interface['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts used traits.
   *
   * @param int $weight The weight of the previous parent.
   */
  private function parseParentsTraits(int &$weight): void
  {
    $traits = $this->extractParentsTraits();
    foreach ($traits as $trait)
    {
      PhpAutoDoc::$dl->padClassInsertParent($this->clsId,
                                                    $trait['name'],
                                                    $trait['full_name'],
                                                    ++$weight,
                                                    0,
                                                    0,
                                                    1,
                                                    $trait['start'],
                                                    $trait['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parsers all properties of the class.
   */
  private function parseProperties(): void
  {
    $properties = $this->extractProperties();

    foreach ($properties as $property)
    {
      if ($property['docblock']===null)
      {
        $docId = null;
      }
      else
      {
        $docId = PhpAutoDoc::$dl->padDocblockInsertDocblock($property['docblock']['doc_line_start'],
                                                                    $property['docblock']['doc_line_end'],
                                                                    $property['docblock']['doc_docblock']);
      }

      PhpAutoDoc::$dl->padClassInsertProperty($this->clsId,
                                                      $docId,
                                                      $property['name'],
                                                      Cast::toManInt($property['is_static']),
                                                      $property['visibility'],
                                                      $property['value'],
                                                      $property['start'],
                                                      $property['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the cls_id_* flags.
   */
  private function parseQualifiers(): void
  {
    $n = preg_match('/^(?<qualifiers>(T_FINAL |T_ABSTRACT )*)(?<type>T_CLASS |T_INTERFACE |T_TRAIT )/',
                    $this->tokens->asString(),
                    $matches);

    if ($n==1)
    {
      PhpAutoDoc::$dl->padClassUpdateFlag($this->clsId,
                                                  Cast::toManInt(TokenMatchHelper::isFinal($matches)),
                                                  Cast::toManInt(TokenMatchHelper::isClass($matches)),
                                                  Cast::toManInt(TokenMatchHelper::isAbstract($matches)),
                                                  Cast::toManInt(TokenMatchHelper::isInterface($matches)),
                                                  Cast::toManInt(TokenMatchHelper::isTrait($matches)),
                                                  1);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
