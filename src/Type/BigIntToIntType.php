<?php

declare(strict_types=1);

namespace App\Type;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class BigIntToIntType extends Type
{
    public const BIGINT_TO_INT = 'bigint_to_int';

    public function getName(): string
    {
        return self::BIGINT_TO_INT;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBigIntTypeDeclarationSQL($column);
    }

    public function getBindingType(): int
    {
        return ParameterType::INTEGER;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return (int) $value;
    }
}
