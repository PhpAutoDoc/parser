
create table PAD_DDL
(
  ddl_script  text not null,
  ddl_version varchar not null
);

/**
 * Table for docblocks.
 */
create table PAD_DOCBLOCK
(
  doc_id         integer primary key asc,
  doc_line_start integer not null,
  doc_line_end   integer not null,
  doc_docblock   text
);

create table PAD_PACKAGE
(
  pck_id           integer primary key asc,
  pck_vendor_name  varchar not null,
  pck_project_name varchar not null
);

create unique index pad_package_idx1 on PAD_PACKAGE(pck_project_name, pck_vendor_name);

create table PAD_FILE
(
  fil_id           integer primary key asc,
  doc_id           integer,
  pck_id           integer,
  fil_path         varchar not null,
  fil_is_parsed    integer not null,
  fil_is_project   integer not null,
  fil_is_seen      integer not null default 1,
  fil_contents     blob not null,
  foreign key (pck_id) references PAD_PACKAGE(pck_id),
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id)
);

create unique index pad_file_idx1 on PAD_FILE(fil_path);

create table PAD_USE
(
  use_id                   integer primary key asc,
  fil_id                   integer not null,
  use_name                 varchar not null collate nocase, -- The name of the item.
  use_namespace            varchar collate nocase,          -- The namespace where the item lives.
  use_fully_qualified_name varchar not null collate nocase, -- The fully qualified name of the item.
  use_is_class             integer not null,
  use_is_function          integer not null,
  use_is_constant          integer not null,
  use_alias                varchar,
  use_line_start           integer not null,                -- The first line of the use statement.
  use_line_end             integer not null,                -- The last line of the use statement.
  foreign key (fil_id) references PAD_FILE(fil_id) on delete cascade
);

create index pad_use_idx1 on PAD_USE(use_name);

/**
 * Table for classes, interfaces, and traits.
 */
create table PAD_CLASS
(
  cls_id                   integer primary key asc,
  doc_id                   integer,
  fil_id                   integer,
  cls_name                 varchar not null collate nocase, -- The name (as used in the source).
  cls_namespace            varchar collate nocase,          -- The namespace where the item lives.
  cls_fully_qualified_name varchar not null collate nocase, -- The fully qualified name of the item.
  cls_is_abstract          integer not null default 0,      -- If 1 the item is declared to be abstract.
  cls_is_class             integer not null default 0,      -- If 1 the item is a class.
  cls_is_final             integer not null default 0,      -- If 1 the item is declared to be final.
  cls_is_interface         integer not null default 0,      -- If 1 the item is an interface.
  cls_is_parsed            integer not null default 0,      -- If 1 the item has been parsed.
  cls_is_trait             integer not null default 0,      -- If 1 the item is a trait.
  cls_is_user_defined      integer not null default 0,      -- If 1 the item is user defined.
  cls_line_start           integer not null,                -- The first line of the item definition.
  cls_line_end             integer not null,                -- The last line of the item definition.
  cls_tokens               blob,                            -- The tokens of the item definition.
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id),
  foreign key (fil_id) references PAD_FILE(fil_id) on delete cascade
);

create index pad_class_idx1 on PAD_CLASS(cls_name);

/**
 * Table for class constants.
 */
create table PAD_CLASS_CONSTANT
(
  cct_id         integer primary key asc,
  cls_id         integer not null,
  doc_id         integer,
  cct_name       varchar not null,                 -- The name of the constant. Constants are case sensitive.
  cct_visibility varchar not null,                 -- The visibility (public,protected,private) of the constant.
  cct_value      varchar not null,
  cct_line_start integer not null,                 -- The first line of the constant definition.
  cct_line_end   integer not null,                 -- The last line of the constant definition.
  foreign key (cls_id) references PAD_CLASS(cls_id) on delete cascade,
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id)
);

create table PAD_CLASS_PARENT
(
  par_id                   integer primary key asc,
  cls_id                   integer not null,
  par_name                 varchar not null collate nocase,
  par_fully_qualified_name varchar not null collate nocase,
  par_weight               integer not null,
  par_is_extending         integer not null default 0,  -- If 1 the class|interface|trait is extending a parent class.
  par_is_implementing      integer not null default 0,  -- If 1 the class|interface|trait is implementing an interface.
  par_is_using             integer not null default 0,  -- If 1 the class|interface|trait is using a trait.
  par_line_start           integer not null,            -- The first line of the parent definition.
  par_line_end             integer not null,            -- The last line of the parent definition.
  foreign key (cls_id) references PAD_CLASS(cls_id) on delete cascade
);

/**
 * Table for class properties (a.k.a. fields).
 */
create table PAD_CLASS_PROPERTY
(
  pty_id              integer primary key asc,
  cls_id              integer not null,
  doc_id              integer,
  pty_name            varchar not null collate nocase,  -- The name of the property.
  pty_is_static       integer not null,                 -- If the property is static 1. Otherwise 0.
  pty_visibility      varchar not null,                 -- The visibility (public,protected,private) of the property.
  pty_value           varchar,                          -- The default value of the property.
  pty_line_start      integer not null,                 -- The first line of the property definition.
  pty_line_end        integer not null,                 -- The last line of the property definition.
  foreign key (cls_id) references PAD_CLASS(cls_id) on delete cascade,
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id)
);

/**
 * Table for class methods (a.k.a. functions).
 */
create table PAD_CLASS_METHOD
(
  mth_id              integer primary key asc,
  cls_id              integer not null,
  doc_id              integer,
  mth_name            varchar not null collate nocase,
  mth_is_abstract     integer not null,
  mth_is_constructor  integer not null,
  mth_is_destructor   integer not null,
  mth_is_final        integer not null,
  mth_is_static       integer not null,
  mth_visibility      varchar not null,                 -- The visibility (public,protected,private) of the method.
  mth_line_start      integer not null,                 -- The first line of the method definition.
  mth_line_end        integer not null,                 -- The last line of the method definition.
  foreign key (cls_id) references PAD_CLASS(cls_id) on delete cascade,
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id)
);

/**
 * Table for arguments of class methods.
 */
create table PAD_CLASS_METHOD_ARGUMENT
(
  mar_id              integer primary key asc,
  mth_id              integer not null,
  mar_ordinal         integer not null,                 -- The ordinal of the argument.
  mar_type_name       varchar collate nocase,           -- The type of the argument.
  mar_name            varchar not null collate nocase,  -- The name of the argument.
  mar_default         varchar collate nocase,           -- The default value of the argument.
  foreign key (mth_id) references PAD_CLASS_METHOD(mth_id) on delete cascade
);

/**
 * Table for constants.
 */
create table PAD_CONSTANT
(
  con_id                   integer primary key asc,
  doc_id                   integer,
  fil_id                   integer,
  con_name                 varchar not null,              -- The name of the constant. Constants are case sensitive.
  con_namespace            varchar collate nocase,        -- The namespace where the constant lives.
  con_fully_qualified_name varchar not null,              -- The fully qualified name of the constant.
  con_value                varchar not null,
  con_line_start           integer not null,              -- The first line of the constant definition.
  con_line_end             integer not null,              -- The last line of the constant definition.
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id),
  foreign key (fil_id) references PAD_FILE(fil_id) on delete cascade
);

/**
 * Table for functions.
 */
create table PAD_FUNCTION
(
  fun_id                   integer primary key asc,
  fil_id                   integer not null,
  doc_id                   integer,
  fun_name                 varchar not null collate nocase,
  fun_namespace            varchar collate nocase,          -- The namespace where the function lives.
  fun_fully_qualified_name varchar not null collate nocase, -- The fully qualified name of the function.
  fun_line_start           integer not null,                -- The first line of the function definition.
  fun_line_end             integer not null,                -- The last line of the function definition.
  foreign key (fil_id) references PAD_FILE(fil_id) on delete cascade,
  foreign key (doc_id) references PAD_DOCBLOCK(doc_id)
);

/**
 * Table for arguments of functions.
 */
create table PAD_FUNCTION_ARGUMENT
(
  far_id              integer primary key asc,
  fun_id              integer not null,
  far_ordinal         integer not null,                 -- The ordinal of the argument.
  far_type_name       varchar collate nocase,           -- The type of the argument.
  far_name            varchar not null collate nocase,  -- The name of the argument.
  far_default         varchar collate nocase,           -- The default value of the argument.
  foreign key (fun_id) references PAD_FUNCTION(fun_id) on delete cascade
);
