<?php

namespace App\Doctrine;

use App\Entity\Sheet\ValueObject\StoredFile;
use App\Entity\ToArray;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ValueObjectArray extends Type
{
    public const VALUE_OBJECT_ARRAY = 'value_object_array';

    public function getName(): string
    {
        return self::VALUE_OBJECT_ARRAY;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return self::VALUE_OBJECT_ARRAY;
    }

    /**
     * @param StoredFile[] $value
     * @return string
     * @throws SerializationFailed
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        try {
            /** @var StoredFile[] $object */
            return json_encode(array_map(fn(StoredFile $v) => $v->toArray(), $value));
        } catch (ExceptionInterface $e) {
            throw SerializationFailed::new($value, 'json', $e->getMessage(), $e);
        }
    }

    /**
     * @param string $value
     * @return StoredFile[]
     * @throws ValueNotConvertible
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        try {
            /** @var array<array<string, string>> $array */
            $array = json_decode($value, true);
            return array_map(fn($item) => StoredFile::fromArray($item), $array);
        } catch (ExceptionInterface $e) {
            throw ValueNotConvertible::new($value, 'json', $e->getMessage(), $e);
        }
    }
}
