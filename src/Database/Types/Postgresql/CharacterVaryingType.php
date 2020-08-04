<?php

namespace ACT\Actadmin\Database\Types\Postgresql;

use ACT\Actadmin\Database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    const NAME = 'character varying';
    const DBTYPE = 'varchar';
}
