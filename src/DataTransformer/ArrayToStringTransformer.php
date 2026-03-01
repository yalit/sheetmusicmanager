<?php

namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @implements DataTransformerInterface<string[], string>
 */
final readonly class ArrayToStringTransformer implements DataTransformerInterface
{
    const DEFAULT_SEPARATOR = ',';

    /**
     * @param non-empty-string $separator
     */
    public function __construct(private string $separator = self::DEFAULT_SEPARATOR)
    {
    }

    /**
     * @param string[] $value
     * @return string
     */
    public function transform(mixed $value): string
    {
        return implode($this->separator, $value);
    }

    /**
     * @param string $value
     * @return string[]
     */
    public function reverseTransform(mixed $value): array
    {
        return array_map(fn(string $v) => trim($v), explode($this->separator, $value));
    }
}
