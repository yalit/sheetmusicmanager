<?php

namespace App\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @implements DataTransformerInterface<string, File>
 */
final readonly class StringToFileTransformer implements DataTransformerInterface
{
    public function __construct(private string $uploadDir)
    {
    }

    /**
     * @param mixed $value
     * @return File[]
     */
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }
        return array_map([$this,'doTransform'], $value);
    }

    private function doTransform(string $value): File
    {
        $filename = $this->uploadDir . DIRECTORY_SEPARATOR . $value;
        if (is_file($filename)) {
            return new File($filename);
        }

        throw new TransformationFailedException(sprintf("File '%s' does not exist.", $filename));
    }

    /**
     * @param File[] $value
     * @return string[]
     */
    public function reverseTransform(mixed $value): mixed
    {
        return array_map([$this,'doReverseTransform'], $value);

    }

    public function doReverseTransform(File $value): string
    {
        if ($value instanceof UploadedFile) {
            $filename = $value->getClientOriginalName();
            $value->move($this->uploadDir, $filename);
            return $filename;
        }

        return $value->getFilename();
    }
}
