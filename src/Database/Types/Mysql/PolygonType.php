<?php

namespace ACT\Actadmin\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ACT\Actadmin\Database\Types\Type;

class PolygonType extends Type
{
    const NAME = 'polygon';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'polygon';
    }
}
