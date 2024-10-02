<?php

declare(strict_types=1);

namespace App\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

class BigIntToIntType extends BigIntType
{
    const BIGINT_TO_INT = 'bigint_to_int';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (int) $value;
    }

    public function getName()
    {
        return self::BIGINT_TO_INT;
    }
}
