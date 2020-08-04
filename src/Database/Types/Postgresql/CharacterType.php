<?php

namespace ACT\Actadmin\Database\Types\Postgresql;

use ACT\Actadmin\Database\Types\Common\CharType;

class CharacterType extends CharType
{
    const NAME = 'character';
    const DBTYPE = 'bpchar';
}
