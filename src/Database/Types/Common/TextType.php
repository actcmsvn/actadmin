<?php

namespace ACT\Actadmin\Database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ACT\Actadmin\Database\Types\Type;

class TextType extends Type
{
    const NAME = 'text';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'text';
    }
}
