<?php
declare(strict_types=1);

namespace PhpAutoDoc\Parser;

use SetBased\Stratum\SqlitePdo\SqlitePdoDataLayer;

/**
 * The data layer.
 */
class DataLayer extends SqlitePdoDataLayer
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects the basic details of a class.
   *
   * @param int|null $pClsId The ID of the class.
   *
   * @return array
   */
  public function padClassGetBasicDetails(?int $pClsId): array
  {
    $replace = [':p_cls_id' => $this->quoteInt($pClsId)];
    $query   = <<< EOT
select cls_id
,      doc_id
,      fil_id
,      cls_name
,      cls_namespace
,      cls_fully_qualified_name
,      cls_tokens
from   PAD_CLASS
where  cls_id = :p_cls_id
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    return $this->executeRow1($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a new class|interface|trait.
   *
   * @param int|null    $pFilId                 The ID of the source file where the class|interface|trait is declared.
   * @param int|null    $pDocId                 The ID of the docblock of the class.
   * @param string|null $pClsName               The name of the class|interface|trait.
   * @param string|null $pClsNamespace          The namespace of the class.
   * @param string|null $pClsFullyQualifiedName The fully qualified name of the class|interface|trait.
   * @param int|null    $pClsLineStart          The first line of the lass|interface|trait definition.
   * @param int|null    $pClsLineEnd            The last line of the lass|interface|trait definition.
   * @param string|null $pClsTokens             The serialized tokens of the class.
   *
   * @return int
   */
  public function padClassInsertClass(?int $pFilId, ?int $pDocId, ?string $pClsName, ?string $pClsNamespace, ?string $pClsFullyQualifiedName, ?int $pClsLineStart, ?int $pClsLineEnd, ?string $pClsTokens): int
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId), ':p_doc_id' => $this->quoteInt($pDocId), ':p_cls_name' => $this->quoteVarchar($pClsName), ':p_cls_namespace' => $this->quoteVarchar($pClsNamespace), ':p_cls_fully_qualified_name' => $this->quoteVarchar($pClsFullyQualifiedName), ':p_cls_line_start' => $this->quoteInt($pClsLineStart), ':p_cls_line_end' => $this->quoteInt($pClsLineEnd), ':p_cls_tokens' => $this->quoteBlob($pClsTokens)];
    $query   = <<< EOT
insert into PAD_CLASS( fil_id
,                      doc_id
,                      cls_name
,                      cls_namespace
,                      cls_fully_qualified_name
,                      cls_line_start
,                      cls_line_end
,                      cls_tokens )
values( :p_fil_id
,       :p_doc_id
,       :p_cls_name
,       :p_cls_namespace
,       :p_cls_fully_qualified_name
,       :p_cls_line_start
,       :p_cls_line_end
,       :p_cls_tokens )
EOT;
    $query = str_repeat(PHP_EOL, 14).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a class constant.
   *
   * @param int|null    $pClsId         The ID of the class of the constant.
   * @param int|null    $pDocId         The ID of the docblock of the constant.
   * @param string|null $pCctName       The name of the constant.
   * @param string|null $pCctVisibility The visibility of the constant.
   * @param string|null $pCctValue      The default value of the constant.
   * @param int|null    $pCctLineStart  The first line of the constant definition.
   * @param int|null    $pCctLineEnd    The last line of the constant definition.
   *
   * @return int
   */
  public function padClassInsertConstant(?int $pClsId, ?int $pDocId, ?string $pCctName, ?string $pCctVisibility, ?string $pCctValue, ?int $pCctLineStart, ?int $pCctLineEnd): int
  {
    $replace = [':p_cls_id' => $this->quoteInt($pClsId), ':p_doc_id' => $this->quoteInt($pDocId), ':p_cct_name' => $this->quoteVarchar($pCctName), ':p_cct_visibility' => $this->quoteVarchar($pCctVisibility), ':p_cct_value' => $this->quoteVarchar($pCctValue), ':p_cct_line_start' => $this->quoteInt($pCctLineStart), ':p_cct_line_end' => $this->quoteInt($pCctLineEnd)];
    $query   = <<< EOT
insert into PAD_CLASS_CONSTANT( cls_id
,                               doc_id
,                               cct_name
,                               cct_visibility
,                               cct_value
,                               cct_line_start
,                               cct_line_end )
values( :p_cls_id
,       :p_doc_id
,       :p_cct_name
,       :p_cct_visibility
,       :p_cct_value
,       :p_cct_line_start
,       :p_cct_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 13).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a method.
   *
   * @param int|null    $pClsId            The ID of the class of the method.
   * @param int|null    $pDocId            The ID of the docblock of the method.
   * @param string|null $pMthName          The name of the method.
   * @param int|null    $pMthIsAbstract    If 1 method is abstract.
   * @param int|null    $pMthIsConstructor If 1 method is object constructor.
   * @param int|null    $pMthIsDestructor  If 1 method is object destructor.
   * @param int|null    $pMthIsFinal       If 1 method is final.
   * @param int|null    $pMthIsStatic      If 1 method is static.
   * @param string|null $pMthVisibility    The visibility of the method.
   * @param int|null    $pMthLineStart     The first line of the constant definition.
   * @param int|null    $pMthLineEnd       The last line of the constant definition.
   *
   * @return int
   */
  public function padClassInsertMethod(?int $pClsId, ?int $pDocId, ?string $pMthName, ?int $pMthIsAbstract, ?int $pMthIsConstructor, ?int $pMthIsDestructor, ?int $pMthIsFinal, ?int $pMthIsStatic, ?string $pMthVisibility, ?int $pMthLineStart, ?int $pMthLineEnd): int
  {
    $replace = [':p_cls_id' => $this->quoteInt($pClsId), ':p_doc_id' => $this->quoteInt($pDocId), ':p_mth_name' => $this->quoteVarchar($pMthName), ':p_mth_is_abstract' => $this->quoteInt($pMthIsAbstract), ':p_mth_is_constructor' => $this->quoteInt($pMthIsConstructor), ':p_mth_is_destructor' => $this->quoteInt($pMthIsDestructor), ':p_mth_is_final' => $this->quoteInt($pMthIsFinal), ':p_mth_is_static' => $this->quoteInt($pMthIsStatic), ':p_mth_visibility' => $this->quoteVarchar($pMthVisibility), ':p_mth_line_start' => $this->quoteInt($pMthLineStart), ':p_mth_line_end' => $this->quoteInt($pMthLineEnd)];
    $query   = <<< EOT
insert into PAD_CLASS_METHOD( cls_id
,                             doc_id
,                             mth_name
,                             mth_is_abstract
,                             mth_is_constructor
,                             mth_is_destructor
,                             mth_is_final
,                             mth_is_static
,                             mth_visibility
,                             mth_line_start
,                             mth_line_end )
values( :p_cls_id
,       :p_doc_id
,       :p_mth_name
,       :p_mth_is_abstract
,       :p_mth_is_constructor
,       :p_mth_is_destructor
,       :p_mth_is_final
,       :p_mth_is_static
 ,      :p_mth_visibility
,       :p_mth_line_start
,       :p_mth_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 17).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a method.
   *
   * @param int|null    $pMthId       The ID of the the method.
   * @param int|null    $pMarOrdinal  The ordinal of the argument.
   * @param string|null $pMarTypeName The type of the argument.
   * @param string|null $pMarName     The name of the argument.
   * @param string|null $pMarDefault  The default value of the argument.
   *
   * @return int
   */
  public function padClassInsertMethodArgument(?int $pMthId, ?int $pMarOrdinal, ?string $pMarTypeName, ?string $pMarName, ?string $pMarDefault): int
  {
    $replace = [':p_mth_id' => $this->quoteInt($pMthId), ':p_mar_ordinal' => $this->quoteInt($pMarOrdinal), ':p_mar_type_name' => $this->quoteVarchar($pMarTypeName), ':p_mar_name' => $this->quoteVarchar($pMarName), ':p_mar_default' => $this->quoteVarchar($pMarDefault)];
    $query   = <<< EOT
insert into PAD_CLASS_METHOD_ARGUMENT( mth_id
,                                      mar_ordinal
,                                      mar_type_name
,                                      mar_name
,                                      mar_default )
values( :p_mth_id
,       :p_mar_ordinal
,       :p_mar_type_name
,       :p_mar_name
,       :p_mar_default );
EOT;
    $query = str_repeat(PHP_EOL, 11).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a parent class|interface|trait for a class|interface|trait.
   *
   * @param int|null    $pClsId                 The ID of the class.
   * @param string|null $pParName               The name of the parent class|interface|trait.
   * @param string|null $pParFullyQualifiedName The fully qualified name of the parent class|interface|trait.
   * @param int|null    $pParWeight             The weight of the parent.
   * @param int|null    $pParIsExtending        If 1 the class|interface|trait is extending a class|interface|trait.
   * @param int|null    $pParIsImplementing     If 1 the class|interface|trait is implementing an interface.
   * @param int|null    $pParIsUsing            If 1 the class|interface|trait is using a trait.
   * @param int|null    $pParLineStart          The first line of the parent definition.
   * @param int|null    $pParLineEnd            The last line of the parent definition.
   */
  public function padClassInsertParent(?int $pClsId, ?string $pParName, ?string $pParFullyQualifiedName, ?int $pParWeight, ?int $pParIsExtending, ?int $pParIsImplementing, ?int $pParIsUsing, ?int $pParLineStart, ?int $pParLineEnd): void
  {
    $replace = [':p_cls_id_' => $this->quoteInt($pClsId), ':p_par_name' => $this->quoteVarchar($pParName), ':p_par_fully_qualified_name' => $this->quoteVarchar($pParFullyQualifiedName), ':p_par_weight' => $this->quoteInt($pParWeight), ':p_par_is_extending' => $this->quoteInt($pParIsExtending), ':p_par_is_implementing' => $this->quoteInt($pParIsImplementing), ':p_par_is_using' => $this->quoteInt($pParIsUsing), ':p_par_line_start' => $this->quoteInt($pParLineStart), ':p_par_line_end' => $this->quoteInt($pParLineEnd)];
    $query   = <<< EOT
insert into PAD_CLASS_PARENT( cls_id
,                             par_name
,                             par_fully_qualified_name
,                             par_weight
,                             par_is_extending
,                             par_is_implementing
,                             par_is_using
,                             par_line_start
,                             par_line_end )
values( :p_cls_id_
,       :p_par_name
,       :p_par_fully_qualified_name
,       :p_par_weight
,       :p_par_is_extending
,       :p_par_is_implementing
,       :p_par_is_using
,       :p_par_line_start
,       :p_par_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 15).$query;

    $this->executeNone($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a class property.
   *
   * @param int|null    $pClsId         The ID of the class of the property.
   * @param int|null    $pDocId         The ID of the docblock of the property.
   * @param string|null $pPtyName       The name of the property.
   * @param int|null    $pPtyIsStatic   If 1 property is static.
   * @param string|null $pPtyVisibility The visibility of the property.
   * @param string|null $pPtyValue      The default value of the property.
   * @param int|null    $pPtyLineStart  The first line of the property definition.
   * @param int|null    $pPtyLineEnd    The last line of the property definition.
   *
   * @return int
   */
  public function padClassInsertProperty(?int $pClsId, ?int $pDocId, ?string $pPtyName, ?int $pPtyIsStatic, ?string $pPtyVisibility, ?string $pPtyValue, ?int $pPtyLineStart, ?int $pPtyLineEnd): int
  {
    $replace = [':p_cls_id' => $this->quoteInt($pClsId), ':p_doc_id' => $this->quoteInt($pDocId), ':p_pty_name' => $this->quoteVarchar($pPtyName), ':p_pty_is_static' => $this->quoteInt($pPtyIsStatic), ':p_pty_visibility' => $this->quoteVarchar($pPtyVisibility), ':p_pty_value' => $this->quoteVarchar($pPtyValue), ':p_pty_line_start' => $this->quoteInt($pPtyLineStart), ':p_pty_line_end' => $this->quoteInt($pPtyLineEnd)];
    $query   = <<< EOT
insert into PAD_CLASS_PROPERTY( cls_id
,                               doc_id
,                               pty_name
,                               pty_is_static
,                               pty_visibility
,                               pty_value
,                               pty_line_start
,                               pty_line_end )
values( :p_cls_id
,       :p_doc_id
,       :p_pty_name
,       :p_pty_is_static
,       :p_pty_visibility
,       :p_pty_value
,       :p_pty_line_start
,       :p_pty_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 14).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Updates the flags of a class|interface|trait.
   *
   * @param int|null $pClsId            The ID of the class.
   * @param int|null $pClsIsAbstract    The is abstract flag.
   * @param int|null $pClsIsClass       The is class flag.
   * @param int|null $pClsIsFinal       The is final flag.
   * @param int|null $pClsIsInterface   The is interface flag.
   * @param int|null $pClsIsTrait       The is trait flag.
   * @param int|null $pClsIsUserDefined The is user defined flag.
   */
  public function padClassUpdateFlag(?int $pClsId, ?int $pClsIsAbstract, ?int $pClsIsClass, ?int $pClsIsFinal, ?int $pClsIsInterface, ?int $pClsIsTrait, ?int $pClsIsUserDefined): void
  {
    $replace = [':p_cls_id' => $this->quoteInt($pClsId), ':p_cls_is_abstract' => $this->quoteInt($pClsIsAbstract), ':p_cls_is_class' => $this->quoteInt($pClsIsClass), ':p_cls_is_final' => $this->quoteInt($pClsIsFinal), ':p_cls_is_interface' => $this->quoteInt($pClsIsInterface), ':p_cls_is_trait' => $this->quoteInt($pClsIsTrait), ':p_cls_is_user_defined' => $this->quoteInt($pClsIsUserDefined)];
    $query   = <<< EOT
update PAD_CLASS
set cls_is_abstract     = :p_cls_is_abstract
,   cls_is_class        = :p_cls_is_class
,   cls_is_final        = :p_cls_is_final
,   cls_is_interface    = :p_cls_is_interface
,   cls_is_trait        = :p_cls_is_trait
,   cls_is_user_defined = :p_cls_is_user_defined
where cls_id = :p_cls_id
EOT;
    $query = str_repeat(PHP_EOL, 13).$query;

    $this->executeNone($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a new constant.
   *
   * @param int|null    $pDocId                 The ID of the docblock of the class.
   * @param int|null    $pFilId                 The ID of the source file where the constant is declared.
   * @param string|null $pConName               The name of the constant.
   * @param string|null $pConNamespace          The namespace of the constant.
   * @param string|null $pConFullyQualifiedName The fully qualified name of the constant.
   * @param string|null $pConValue              The value of the constant.
   * @param int|null    $pConLineStart          The first line of the constant declaration.
   * @param int|null    $pConLineEnd            The last line of the constant declaration.
   *
   * @return int
   */
  public function padConstantInsertConstant(?int $pDocId, ?int $pFilId, ?string $pConName, ?string $pConNamespace, ?string $pConFullyQualifiedName, ?string $pConValue, ?int $pConLineStart, ?int $pConLineEnd): int
  {
    $replace = [':p_doc_id' => $this->quoteInt($pDocId), ':p_fil_id' => $this->quoteInt($pFilId), ':p_con_name' => $this->quoteVarchar($pConName), ':p_con_namespace' => $this->quoteVarchar($pConNamespace), ':p_con_fully_qualified_name' => $this->quoteVarchar($pConFullyQualifiedName), ':p_con_value' => $this->quoteVarchar($pConValue), ':p_con_line_start' => $this->quoteInt($pConLineStart), ':p_con_line_end' => $this->quoteInt($pConLineEnd)];
    $query   = <<< EOT
insert into PAD_CONSTANT( doc_id
,                         fil_id
,                         con_name
,                         con_namespace
,                         con_fully_qualified_name
,                         con_value
,                         con_line_start
,                         con_line_end )
values( :p_doc_id
,       :p_fil_id
,       :p_con_name
,       :p_con_namespace
,       :p_con_fully_qualified_name
,       :p_con_value
,       :p_con_line_start
,       :p_con_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 14).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects the DDL script and application version of the database.
   *
   * @return array
   */
  public function padDllGetDll(): array
  {
    $query = <<< EOT
select ddl_script
,      ddl_version
from   PAD_DDL
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    return $this->executeRow1($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a new docblock.
   *
   * @param int|null    $pDocLineStart The number of the first line of the docblock in the source file.
   * @param int|null    $pDocLineEnd   The number of the last line of the docblock in the source file.
   * @param string|null $pDocDocblock  The docblock.
   *
   * @return int
   */
  public function padDocblockInsertDocblock(?int $pDocLineStart, ?int $pDocLineEnd, ?string $pDocDocblock): int
  {
    $replace = [':p_doc_line_start' => $this->quoteInt($pDocLineStart), ':p_doc_line_end' => $this->quoteInt($pDocLineEnd), ':p_doc_docblock' => $this->quoteVarchar($pDocDocblock)];
    $query   = <<< EOT
insert into PAD_DOCBLOCK( doc_line_start
,                         doc_line_end
,                         doc_docblock )
values( :p_doc_line_start
,       :p_doc_line_end
,       :p_doc_docblock)
EOT;
    $query = str_repeat(PHP_EOL, 9).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Deletes an unseen files.
   */
  public function padFileDeleteAllUnseen(): void
  {
    $query = <<< EOT
delete from PAD_FILE
where fil_is_seen = 0
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    $this->executeNone($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Deletes an obsolete source file.
   *
   * @param int|null $pFilId The ID of the source file.
   */
  public function padFileDeleteFile(?int $pFilId): void
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId)];
    $query   = <<< EOT
delete from PAD_FILE
where fil_id = :p_fil_id
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    $this->executeNone($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects a source file given the ID of the file.
   *
   * @param int|null $pFilId The ID of the source file.
   *
   * @return array
   */
  public function padFileGetFile(?int $pFilId): array
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId)];
    $query   = <<< EOT
select fil_id
,      fil_path
,      fil_is_parsed
,      fil_is_project
,      fil_contents
from   PAD_FILE
where  fil_id = :p_fil_id
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    return $this->executeRow1($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a new source file.
   *
   * @param int|null    $pPckId        The ID of the package to which the source files belongs.
   * @param string|null $pFilPath      The path to the source file.
   * @param int|null    $pFilIsProject If 1 the file belongs to the project to be documented.
   * @param string|null $pFilContents  The source code
   *
   * @return int
   */
  public function padFileInsertFile(?int $pPckId, ?string $pFilPath, ?int $pFilIsProject, ?string $pFilContents): int
  {
    $replace = [':p_pck_id' => $this->quoteInt($pPckId), ':p_fil_path' => $this->quoteVarchar($pFilPath), ':p_fil_is_project' => $this->quoteInt($pFilIsProject), ':p_fil_contents' => $this->quoteBlob($pFilContents)];
    $query   = <<< EOT
insert into PAD_FILE( pck_id
,                     fil_path
,                     fil_is_parsed
,                     fil_is_project
,                     fil_contents )
values( :p_pck_id
,       :p_fil_path
,       0
,       :p_fil_is_project
,       :p_fil_contents )
EOT;
    $query = str_repeat(PHP_EOL, 10).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Search for a source file given a path to a file.
   *
   * @param string|null $pFilPath The path to the source file.
   *
   * @return array|null
   */
  public function padFileSearchByPath(?string $pFilPath): ?array
  {
    $replace = [':p_fil_path' => $this->quoteVarchar($pFilPath)];
    $query   = <<< EOT
select fil_id
,      fil_contents
from   PAD_FILE
where  fil_path = :p_fil_path
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    return $this->executeRow0($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Marks all file as unseen.
   */
  public function padFileUpdateAllUnseen(): void
  {
    $query = <<< EOT
update PAD_FILE
set    fil_is_seen = 0
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    $this->executeNone($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Updates the docblock of a source file.
   *
   * @param int|null $pFilId The ID of the source file.
   * @param int|null $pDocId The ID of the docblock.
   */
  public function padFileUpdateDocblock(?int $pFilId, ?int $pDocId): void
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId), ':p_doc_id' => $this->quoteInt($pDocId)];
    $query   = <<< EOT
update PAD_FILE
set    doc_id = :p_doc_id
where  fil_id = :p_fil_id
EOT;
    $query = str_repeat(PHP_EOL, 8).$query;

    $this->executeNone($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Marks a file as been parsed.
   *
   * @param int|null $pFilId The ID of the source file.
   */
  public function padFileUpdateIsParsed(?int $pFilId): void
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId)];
    $query   = <<< EOT
update PAD_FILE
set    fil_is_parsed = 1
where  fil_id = :p_fil_id
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    $this->executeNone($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Marks a file as been seen.
   *
   * @param int|null $pFilId The ID of the source file.
   */
  public function padFileUpdateIsSeen(?int $pFilId): void
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId)];
    $query   = <<< EOT
update PAD_FILE
set    fil_is_seen = 1
where  fil_id = :p_fil_id
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    $this->executeNone($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a function.
   *
   * @param int|null    $pFilId                 The ID of the source file where the function is declared.
   * @param int|null    $pDocId                 The ID of the docblock of the function.
   * @param string|null $pFunName               The name of the function.
   * @param string|null $pFunNamespace          The namespace of the function.
   * @param string|null $pFunFullyQualifiedName The fully qualified name of the function.
   * @param int|null    $pFunLineStart          The first line of the function definition.
   * @param int|null    $pFunLineEnd            The last line of the function definition.
   *
   * @return int
   */
  public function padFunctionInsertFunction(?int $pFilId, ?int $pDocId, ?string $pFunName, ?string $pFunNamespace, ?string $pFunFullyQualifiedName, ?int $pFunLineStart, ?int $pFunLineEnd): int
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId), ':p_doc_id' => $this->quoteInt($pDocId), ':p_fun_name' => $this->quoteVarchar($pFunName), ':p_fun_namespace' => $this->quoteVarchar($pFunNamespace), ':p_fun_fully_qualified_name' => $this->quoteVarchar($pFunFullyQualifiedName), ':p_fun_line_start' => $this->quoteInt($pFunLineStart), ':p_fun_line_end' => $this->quoteInt($pFunLineEnd)];
    $query   = <<< EOT
insert into PAD_FUNCTION( fil_id
,                         doc_id
,                         fun_name
,                         fun_namespace
,                         fun_fully_qualified_name
,                         fun_line_start
,                         fun_line_end  )
values( :p_fil_id
,       :p_doc_id
,       :p_fun_name
,       :p_fun_namespace
,       :p_fun_fully_qualified_name
,       :p_fun_line_start
,       :p_fun_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 13).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a function argument.
   *
   * @param int|null    $pFunId       The ID of the the function.
   * @param int|null    $pFarOrdinal  The ordinal of the argument.
   * @param string|null $pFarTypeName The type of the argument.
   * @param string|null $pFarName     The name of the argument.
   * @param string|null $pFarDefault  The default value of the argument.
   *
   * @return int
   */
  public function padFunctionInsertFunctionArgument(?int $pFunId, ?int $pFarOrdinal, ?string $pFarTypeName, ?string $pFarName, ?string $pFarDefault): int
  {
    $replace = [':p_fun_id' => $this->quoteInt($pFunId), ':p_far_ordinal' => $this->quoteInt($pFarOrdinal), ':p_far_type_name' => $this->quoteVarchar($pFarTypeName), ':p_far_name' => $this->quoteVarchar($pFarName), ':p_far_default' => $this->quoteVarchar($pFarDefault)];
    $query   = <<< EOT
insert into PAD_FUNCTION_ARGUMENT( fun_id
,                                  far_ordinal
,                                  far_type_name
,                                  far_name
,                                  far_default )
values( :p_fun_id
,       :p_far_ordinal
,       :p_far_type_name
,       :p_far_name
,       :p_far_default );
EOT;
    $query = str_repeat(PHP_EOL, 11).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true if a table exists. Otherwise returns false.
   *
   * @param string|null $pTableName The name of the table.
   *
   * @return bool
   */
  public function padMiscTableExists(?string $pTableName): bool
  {
    $replace = [':p_table_name' => $this->quoteVarchar($pTableName)];
    $query   = <<< EOT
select count(*)
from   sqlite_master
where  type = 'table'
and    name = :p_table_name
EOT;
    $query = str_repeat(PHP_EOL, 8).$query;

    return !empty($this->executeSingleton1($query, $replace));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a new package.
   *
   * @param string|null $pPckVendorName  The vendor name.
   * @param string|null $pPckProjectName The project name.
   *
   * @return int
   */
  public function padPackageInsertPackage(?string $pPckVendorName, ?string $pPckProjectName): int
  {
    $replace = [':p_pck_vendor_name' => $this->quoteVarchar($pPckVendorName), ':p_pck_project_name' => $this->quoteVarchar($pPckProjectName)];
    $query   = <<< EOT
insert into PAD_PACKAGE( pck_vendor_name
,                        pck_project_name )
values( :p_pck_vendor_name
,       :p_pck_project_name )
EOT;
    $query = str_repeat(PHP_EOL, 8).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Search for package.
   *
   * @param string|null $pPckVendorName  The vendor name.
   * @param string|null $pPckProjectName The project name.
   *
   * @return array|null
   */
  public function padPackageSearch(?string $pPckVendorName, ?string $pPckProjectName): ?array
  {
    $replace = [':p_pck_vendor_name' => $this->quoteVarchar($pPckVendorName), ':p_pck_project_name' => $this->quoteVarchar($pPckProjectName)];
    $query   = <<< EOT
select pck_id
,      pck_vendor_name
,      pck_project_name
from   PAD_PACKAGE
where  pck_vendor_name  = :p_pck_vendor_name
and    pck_project_name = :p_pck_project_name
EOT;
    $query = str_repeat(PHP_EOL, 8).$query;

    return $this->executeRow0($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects all packages that are used in the project code.
   *
   * @return array[]
   */
  public function padPackagesGetAllUsedInCode(): array
  {
    $query = <<< EOT
select distinct pck_vendor_name
,               pck_project_name
from
(
    select distinct pck.pck_vendor_name
    ,               pck.pck_project_name
    from      PAD_USE     use
    join      PAD_FILE    fl1  on  fl1.fil_id = use.fil_id
    join      PAD_CLASS   cls  on  cls.cls_fully_qualified_name = use.use_fully_qualified_name
    join      PAD_FILE    fl2  on  fl2.fil_id = cls.fil_id
    left join PAD_PACKAGE pck  on  pck.pck_id = fl2.pck_id
    where  use.use_is_class = 1
    and    instr(use.use_fully_qualified_name, '\') > 1
    and    fl1.fil_is_project = 1
    and    fl2.fil_is_project = 0

    union all

    select distinct pck.pck_vendor_name
    ,               pck.pck_project_name
    from      PAD_USE      use
    join      PAD_FILE     fl1  on  fl1.fil_id = use.fil_id
    join      PAD_CONSTANT con  on  con.con_fully_qualified_name = use.use_fully_qualified_name
    join      PAD_FILE     fl2  on  fl2.fil_id = con.fil_id
    left join PAD_PACKAGE  pck  on  pck.pck_id = fl2.pck_id
    where  use.use_is_constant = 1
    and    instr(use.use_fully_qualified_name, '\') > 1
    and    fl1.fil_is_project = 1
    and    fl2.fil_is_project = 0

    union all

    select distinct pck.pck_vendor_name
    ,               pck.pck_project_name
    from      PAD_USE      use
    join      PAD_FILE     fl1  on  fl1.fil_id = use.fil_id
    join      PAD_FUNCTION fun  on  fun.fun_fully_qualified_name = use.use_fully_qualified_name
    join      PAD_FILE     fl2  on  fl2.fil_id = fun.fil_id
    left join PAD_PACKAGE  pck  on  pck.pck_id = fl2.pck_id
    where  use.use_is_function = 1
    and    instr(use.use_name, '\') > 1
    and    fl1.fil_is_project = 1
    and    fl2.fil_is_project = 0
) t
order by pck_vendor_name
,        pck_project_name
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    return $this->executeRows($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Selects all files that are using a package.
   *
   * @param int|null $pPckId The ID of the package.
   *
   * @return array[]
   */
  public function padPackagesGetUsages(?int $pPckId): array
  {
    $replace = [':p_pck_id' => $this->quoteInt($pPckId)];
    $query   = <<< EOT
select fil_path
,      min(use_line_start) use_line_start
from
(
    select fl1.fil_path
    ,      use.use_line_start
    from      PAD_USE     use
    join      PAD_FILE    fl1  on  fl1.fil_id = use.fil_id
    join      PAD_CLASS   cls  on  cls.cls_fully_qualified_name = use.use_fully_qualified_name
    join      PAD_FILE    fl2  on  fl2.fil_id = cls.fil_id
    left join PAD_PACKAGE pck  on  pck.pck_id = fl2.pck_id
    where  use.use_is_class = 1
    and    instr(use.use_fully_qualified_name, '\') > 1
    and    fl1.fil_is_project = 1
    and    fl2.fil_is_project = 0
    and    pck.pck_id         = :p_pck_id

    union all

    select fl1.fil_path
    ,      use.use_line_start
    from      PAD_USE      use
    join      PAD_FILE     fl1  on  fl1.fil_id = use.fil_id
    join      PAD_CONSTANT con  on  con.con_fully_qualified_name = use.use_fully_qualified_name
    join      PAD_FILE     fl2  on  fl2.fil_id = con.fil_id
    left join PAD_PACKAGE  pck  on  pck.pck_id = fl2.pck_id
    where  use.use_is_constant = 1
    and    instr(use.use_fully_qualified_name, '\') > 1
    and    fl1.fil_is_project = 1
    and    fl2.fil_is_project = 0
    and    pck.pck_id         = :p_pck_id

    union all

    select fl1.fil_path
    ,      use.use_line_start
    from      PAD_USE      use
    join      PAD_FILE     fl1  on  fl1.fil_id = use.fil_id
    join      PAD_FUNCTION fun  on  fun.fun_fully_qualified_name = use.use_fully_qualified_name
    join      PAD_FILE     fl2  on  fl2.fil_id = fun.fil_id
    left join PAD_PACKAGE  pck  on  pck.pck_id = fl2.pck_id
    where  use.use_is_function = 1
    and    instr(use.use_name, '\') > 1
    and    fl1.fil_is_project = 1
    and    fl2.fil_is_project = 0
    and    pck.pck_id         = :p_pck_id
) t
group by fil_path
order by fil_path
EOT;
    $query = str_repeat(PHP_EOL, 7).$query;

    return $this->executeRows($query, $replace);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts a use.
   *
   * @param int|null    $pFilId                 The ID of the source file.
   * @param string|null $pUseName               The name of the item
   * @param string|null $pUseNamespace          The The namespace where the item lives.
   * @param string|null $pUseFullyQualifiedName The fully qualified name of the item.
   * @param int|null    $pUseIsClass            The alias/import is a class.
   * @param int|null    $pUseIsFunction         The alias/import is a function.
   * @param int|null    $pUseIsConstant         The alias/import is a constant.
   * @param string|null $pUseAlias              The alias.
   * @param int|null    $pUseLineStart          The first line of the use statement.
   * @param int|null    $pUseLineEnd            The last line of the use statement.
   *
   * @return int
   */
  public function padUseInsertUse(?int $pFilId, ?string $pUseName, ?string $pUseNamespace, ?string $pUseFullyQualifiedName, ?int $pUseIsClass, ?int $pUseIsFunction, ?int $pUseIsConstant, ?string $pUseAlias, ?int $pUseLineStart, ?int $pUseLineEnd): int
  {
    $replace = [':p_fil_id' => $this->quoteInt($pFilId), ':p_use_name' => $this->quoteVarchar($pUseName), ':p_use_namespace' => $this->quoteVarchar($pUseNamespace), ':p_use_fully_qualified_name' => $this->quoteVarchar($pUseFullyQualifiedName), ':p_use_is_class' => $this->quoteInt($pUseIsClass), ':p_use_is_function' => $this->quoteInt($pUseIsFunction), ':p_use_is_constant' => $this->quoteInt($pUseIsConstant), ':p_use_alias' => $this->quoteVarchar($pUseAlias), ':p_use_line_start' => $this->quoteInt($pUseLineStart), ':p_use_line_end' => $this->quoteInt($pUseLineEnd)];
    $query   = <<< EOT
insert into PAD_USE( fil_id
,                    use_name
,                    use_namespace
,                    use_fully_qualified_name
,                    use_is_class
,                    use_is_function
,                    use_is_constant
,                    use_alias
,                    use_line_start
,                    use_line_end)
values( :p_fil_id
,       :p_use_name
,       :p_use_namespace
,       :p_use_fully_qualified_name
,       :p_use_is_class
,       :p_use_is_function
,       :p_use_is_constant
,       :p_use_alias
,       :p_use_line_start
,       :p_use_line_end )
EOT;
    $query = str_repeat(PHP_EOL, 16).$query;

    $this->executeNone($query, $replace);
    return $this->lastInsertId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Deletes tokens.
   */
  public function padVacuumDeleteTokens(): void
  {
    $query = <<< EOT
update PAD_CLASS
set cls_tokens = null
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    $this->executeNone($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Deletes obsolete docblocks.
   */
  public function padVacuumDocblock(): void
  {
    $query = <<< EOT
create temporary table TMP_DOCBLOCK
(
  doc_id integer primary key
)
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_CLASS
where doc_id is not null
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_CLASS_CONSTANT
where doc_id is not null
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_CLASS_METHOD
where doc_id is not null
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_CLASS_PROPERTY
where doc_id is not null
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_CONSTANT
where doc_id is not null
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_FILE
where doc_id is not null
;

insert into TMP_DOCBLOCK(doc_id)
select doc_id
from   PAD_FUNCTION
where doc_id is not null
;

delete from PAD_DOCBLOCK
where doc_id not in ( select doc_id from TMP_DOCBLOCK)
;

drop table TMP_DOCBLOCK;
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    $this->executeNone($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Rebuilds the database file.
   */
  public function padVacuumVacuum(): void
  {
    $query = <<< EOT
vacuum
EOT;
    $query = str_repeat(PHP_EOL, 5).$query;

    $this->executeNone($query);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
