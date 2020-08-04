<?php

namespace ACT\Actadmin\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ACT\Actadmin\Database\Types\Type;

class InetType extends Type
{
    const NAME = 'inet';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'inet';
    }
}
