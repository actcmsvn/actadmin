<?php

namespace ACT\Actadmin\Database\Types\Sqlite;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ACT\Actadmin\Database\Types\Type;

class RealType extends Type
{
    const NAME = 'real';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'real';
    }
}
