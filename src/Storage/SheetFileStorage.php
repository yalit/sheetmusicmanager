<?php

namespace App\Storage;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class SheetFileStorage
{
    public function __construct(
        private string     $projectDir,
        private string     $uploadDir,
        private Filesystem $filesystem,
    ) {}

    public function absolutePath(string $filename): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . $filename;
    }

    public function directory(): string
    {
        return $this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir;
    }

    public function webPath(string $filename): string
    {
        return '/' . preg_replace('#^public/#', '', $this->uploadDir) . '/' . $filename;
    }

    public function save(UploadedFile $file): string
    {
        $filename = $file->getClientOriginalName();
        $file->move($this->directory(), $filename);

        return $filename;
    }

    public function delete(string $filename): void
    {
        $this->filesystem->remove($this->absolutePath($filename));
    }
}
