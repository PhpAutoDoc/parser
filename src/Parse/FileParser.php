<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser\Parse;

use PhpAutoDoc\Parser\Helper\NamespaceHelper;
use PhpAutoDoc\Parser\Helper\TokenMatchHelper;
use PhpAutoDoc\Parser\Parse\Event\ProjectClassFoundEvent;
use PhpAutoDoc\Parser\PhpAutoDoc;
use SetBased\Helper\Cast;

/**
 * Parses a single source file.
 */
class FileParser
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The namespace of the source file.
   *
   * @var string|null
   */
  private $namespace;

  /**
   * The details of the source file.
   *
   * @var array
   */
  private $source;

  /**
   * The token helper object.
   *
   * @var Tokens
   */
  private $tokens;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int $filId The ID of the source file.
   */
  public function __construct(int $filId)
  {
    $this->source = PhpAutoDoc::$dl->padFileGetFile($filId);
    $this->tokens = new Tokens($this->source['fil_contents']);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the source file.
   */
  public function parse(): void
  {
    PhpAutoDoc::$io->text(sprintf('Parsing source file <fso>%s</fso>', $this->source['fil_path']));

    $this->parseNamespace();
    $this->parseUses();
    $this->parseConstants();
    $this->handleDocBloc();
    $this->parseClasses();
    $this->parseFunctions();

    PhpAutoDoc::$dl->padFileMarkProcessed($this->source['fil_id']);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the declared classes, interfaces, and traits from the source file.
   *
   * @return array
   */
  private function extractClasses(): array
  {
    $classes = [];

    $tokens = $this->tokens->withoutBlocks();
    preg_match_all('/(?<docblock>T_DOC_COMMENT )?(T_FINAL |T_ABSTRACT )*(?<type>T_CLASS |T_INTERFACE |T_TRAIT )(?<name>T_STRING )[^{]*{ } /',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches as $match)
    {
      $classTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
      $lines       = $classTokens->lines();

      $classes[] = ['docblock' => TokenMatchHelper::docblockDetails($match, $tokens),
                    'name'     => TokenMatchHelper::code('name', $match, $tokens),
                    'start'    => $lines['start'],
                    'end'      => $lines['end'],
                    'tokens'   => $classTokens];
    }

    return $classes;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the constants declares using const.
   *
   * @return array
   */
  private function extractConstantsConst(): array
  {
    $consts = [];

    $tokens = $this->tokens->withoutCurlyParenthesizedBlocks();

    preg_match_all('/(?<docblock>T_DOC_COMMENT )?(?<const>T_CONST )(?<name>T_STRING )= (?<value>([^;]*)) ; /',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches as $match)
    {
      $constantTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
      $lines          = $constantTokens->lines();
      $name           = TokenMatchHelper::code('name', $match, $tokens);

      $consts[] = ['docblock'  => TokenMatchHelper::docblockDetails($match, $tokens),
                   'name'      => $name,
                   'namespace' => $this->namespace,
                   'full_name' => NamespaceHelper::fullyQualifiedName($this->namespace, $name),
                   'value'     => TokenMatchHelper::code('value', $match, $tokens, $this->tokens),
                   'start'     => $lines['start'],
                   'end'       => $lines['end'],
                   'tokens'    => $constantTokens];
    }

    return $consts;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the constants declares using define.
   *
   * @return array
   */
  private function extractConstantsDefine(): array
  {
    $defines = [];

    $tokens = $this->tokens->withoutCurlyParenthesizedBlocks();

    preg_match_all('/(?<docblock>T_DOC_COMMENT )?(?<define>T_STRING )\( (?<name>T_CONSTANT_ENCAPSED_STRING ), (?<value>([^;]*) )\) ; /',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches as $match)
    {
      $define = TokenMatchHelper::code('define', $match, $tokens);
      if (mb_strtolower($define)=='define')
      {
        $constantTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
        $lines          = $constantTokens->lines();
        $name           = TokenMatchHelper::code('name', $match, $tokens);
        $name           = mb_substr($name, 1, -1);

        [$namespace, $name, $fullName] = NamespaceHelper::split($name);

        $defines[] = ['docblock'  => TokenMatchHelper::docblockDetails($match, $tokens),
                      'name'      => $name,
                      'namespace' => $namespace,
                      'full_name' => $fullName,
                      'value'     => TokenMatchHelper::code('value', $match, $tokens, $this->tokens),
                      'start'     => $lines['start'],
                      'end'       => $lines['end'],
                      'tokens'    => $constantTokens];
      }
    }

    return $defines;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the file level docblock of the source file.
   *
   * A file level docblock is a docblock:
   * * That is the first docblock.
   * * Followed by one of:
   *    ** namespace
   *    ** use
   *    ** another docblock
   *
   * @return array|null
   */
  private function extractDocblock(): ?array
  {
    $n = preg_match('/^T_OPEN_TAG (T_DECLARE \(([^()]|(?R))*\);)*(?<docblock>T_DOC_COMMENT )(T_NAMESPACE |T_USE |T_DOC_COMMENT )/',
                    $this->tokens->asString(),
                    $matches,
                    PREG_OFFSET_CAPTURE);

    if ($n==1)
    {
      $offset   = $matches['docblock'][1];
      $key      = $this->tokens->keyByOffset($offset);
      $lines    = $this->tokens->lines($key);
      $docblock = ['doc_line_start' => $lines['start'],
                   'doc_line_end'   => $lines['end'],
                   'doc_docblock'   => $this->tokens->code($key, $key)];
    }
    else
    {
      $docblock = null;
    }

    return $docblock;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the declared functions from the source file.
   *
   * @return array
   */
  private function extractFunctions(): array
  {
    $functions = [];

    $tokens = $this->tokens->withoutBlocks();

    preg_match_all('/(?<docblock>T_DOC_COMMENT )?T_FUNCTION (?<name>T_STRING )\( \) { } /',
                   $tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches as $match)
    {
      $functionTokens = TokenMatchHelper::codeBlock($match, $tokens, $this->tokens);
      $lines          = $functionTokens->lines();

      $functions[] = ['docblock' => TokenMatchHelper::docblockDetails($match, $tokens),
                      'name'     => TokenMatchHelper::code('name', $match, $tokens),
                      'start'    => $lines['start'],
                      'end'      => $lines['end'],
                      'tokens'   => $functionTokens];
    }

    return $functions;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the namespace of the source file.
   *
   * @return string
   */
  private function extractNamespace(): ?string
  {
    $namespace = null;

    $n = preg_match('/T_NAMESPACE (?<namespace>(T_NS_SEPARATOR |T_STRING )+);/',
                    $this->tokens->asString(),
                    $matches,
                    PREG_OFFSET_CAPTURE);
    if ($n==1)
    {
      $offset1 = $matches['namespace'][1];
      $offset2 = $offset1 + Tokens::offsetLastToken($matches['namespace'][0]);
      $key1    = $this->tokens->keyByOffset($offset1);
      $key2    = $this->tokens->keyByOffset($offset2);

      $namespace = $this->tokens->code($key1, $key2);
    }

    return $namespace;
  }
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the uses from the source file.
   *
   * @return array
   */
  private function extractUses(): array
  {
    $uses = [];

    preg_match_all('/T_USE (?<type>T_FUNCTION |T_CONST )?(?<name>(T_NS_SEPARATOR |T_STRING )+)(T_AS (?<alias>T_STRING ))?; /',
                   $this->tokens->asString(),
                   $matches,
                   PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    foreach ($matches as $match)
    {
      $useTokens = TokenMatchHelper::codeBlock($match, $this->tokens, $this->tokens);
      $lines     = $useTokens->lines();

      $is_class    = ($match['type'][0]==='');
      $is_function = ($match['type'][0]==='T_FUNCTION ');
      $is_constant = ($match['type'][0]==='T_CONST ');

      $uses[] = ['name'        => ltrim(TokenMatchHelper::code('name', $match, $this->tokens), '\\'),
                 'is_class'    => $is_class,
                 'is_function' => $is_function,
                 'is_constant' => $is_constant,
                 'alias'       => TokenMatchHelper::code('alias', $match, $this->tokens),
                 'start'       => $lines['start'],
                 'end'         => $lines['end']];
    }

    return $uses;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extract the file level docblock from the source file and stores the docblock in the database.
   */
  private function handleDocBloc()
  {
    $docblock = $this->extractDocblock();

    if ($docblock!==null)
    {
      $docId = PhpAutoDoc::$dl->padDocblockInsertDocblock($docblock['doc_line_start'],
                                                                  $docblock['doc_line_end'],
                                                                  $docblock['doc_docblock']);
      PhpAutoDoc::$dl->padFileUpdateDocblock($this->source['fil_id'], $docId);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Insert a docblock.
   *
   * @param array $item The metadata of an item with an optional docblock.
   *
   * @return int|null The ID of the docblock.
   */
  private function insertDockBlock(array $item)
  {
    if ($item['docblock']===null)
    {
      $docId = null;
    }
    else
    {
      $docId = PhpAutoDoc::$dl->padDocblockInsertDocblock($item['docblock']['doc_line_start'],
                                                                  $item['docblock']['doc_line_end'],
                                                                  $item['docblock']['doc_docblock']);
    }

    return $docId;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Detects the declared classes, interfaces, and traits from the source file.
   */
  private function parseClasses()
  {
    $classes = $this->extractClasses();

    foreach ($classes as $class)
    {
      $fullName = NamespaceHelper::fullyQualifiedName($this->namespace, $class['name']);
      $clsId    = PhpAutoDoc::$dl->padClassInsertClass($this->source['fil_id'],
                                                               $this->insertDockBlock($class),
                                                               $class['name'],
                                                               $this->namespace,
                                                               $fullName,
                                                               $class['start'],
                                                               $class['end'],
                                                               serialize($class['tokens']));

      if ($this->source['fil_is_project']==1)
      {
        PhpAutoDoc::$eventDispatcher->notify(new ProjectClassFoundEvent($clsId));
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses all declared constants.
   */
  private function parseConstants()
  {
    $constants1 = $this->extractConstantsDefine();
    $constants2 = $this->extractConstantsConst();
    $constants  = array_merge($constants1, $constants2);
    foreach ($constants as $constant)
    {
      PhpAutoDoc::$dl->padConstantInsertConstant($this->insertDockBlock($constant),
                                                         $this->source['fil_id'],
                                                         $constant['name'],
                                                         $constant['namespace'],
                                                         $constant['full_name'],
                                                         $constant['value'],
                                                         $constant['start'],
                                                         $constant['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Detects the declared functions from the source file.
   */
  private function parseFunctions()
  {
    $functions = $this->extractFunctions();
    foreach ($functions as $function)
    {
      $fullName = NamespaceHelper::fullyQualifiedName($this->namespace, $function['name']);
      PhpAutoDoc::$dl->padFunctionInsertFunction($this->source['fil_id'],
                                                         $this->insertDockBlock($function),
                                                         $function['name'],
                                                         $this->namespace,
                                                         $fullName,
                                                         $function['start'],
                                                         $function['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the namespace statement in the source file and stores the namespace in the database.
   */
  private function parseNamespace(): void
  {
    $this->namespace = $this->extractNamespace();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parse the use statement int the source file and stores them in the database.
   */
  private function parseUses(): void
  {
    $uses = $this->extractUses();
    foreach ($uses as $use)
    {
      PhpAutoDoc::$dl->padUseInsertUse($this->source['fil_id'],
                                               $use['name'],
                                               Cast::toManInt($use['is_class']),
                                               Cast::toManInt($use['is_function']),
                                               Cast::toManInt($use['is_constant']),
                                               $use['alias'],
                                               $use['start'],
                                               $use['end']);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
