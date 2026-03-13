<?php

namespace App\Entity\ValueObject;

class StoredFile
{
    public function __construct(
        public string $name,
        public string $filename,
    ) {}

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'filename' => $this->filename,
        ];
    }

    /**
     * @param array<string, string> $array
     */
    public static function fromArray(array $array): StoredFile
    {
        return new StoredFile($array['name'], $array['filename']);
    }
}
