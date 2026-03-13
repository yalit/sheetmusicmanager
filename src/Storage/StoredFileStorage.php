<?php

namespace App\Storage;

use App\Entity\ValueObject\StoredFile;
use DateTimeImmutable;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

final readonly class StoredFileStorage
{
    public function __construct(
        private string     $projectDir,
        private string     $uploadDir,
        private Filesystem $filesystem,
    ) {}

    public function absolutePath(StoredFile $file): string
    {
        return $this->getAbsolutePath($file->filename);
    }

    public function webPath(StoredFile $file): string
    {
        return str_replace('public', '', $this->uploadDir).'/'.$file->filename;
    }

    public function save(UploadedFile $file): StoredFile
    {
        $name = $file->getClientOriginalName();
        $filename = (new DateTimeImmutable())->format('YmdHis') . '_' . Uuid::v4()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($this->directory(), $filename);

        return new StoredFile($name, $filename);
    }

    public function delete(StoredFile $file): void
    {
        $this->filesystem->remove($this->absolutePath($file));
    }

    private function directory(): string
    {
        return $this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir;
    }

    private function getAbsolutePath(string $filename): string
    {
        return $this->directory() . DIRECTORY_SEPARATOR . $filename;
    }
}
