<?php

namespace ACT\Actadmin\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ACT\Actadmin\Database\Types\Type;

class UuidType extends Type
{
    const NAME = 'uuid';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'uuid';
    }
}
