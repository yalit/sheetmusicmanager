<?php

namespace App\Twig;

use App\Entity\ValueObject\StoredFile;
use App\Storage\StoredFileStorage;
use Twig\Attribute\AsTwigFunction;

readonly class StoredFileDisplayExtension
{
    public function __construct(private StoredFileStorage $storage)
    {
    }

    #[AsTwigFunction('filesize')]
    public function fileSize(StoredFile $storedFile): string
    {
        $bytes = filesize($this->storage->absolutePath($storedFile));
        if ($bytes === false || $bytes < 1024) {
            return ($bytes ?: 0) . ' o';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' Ko';
        }
        return round($bytes / (1024 * 1024), 1) . ' Mo';
    }

    #[AsTwigFunction('webpath')]
    public function webpath(StoredFile $storedFile): string
    {
        return $this->storage->webPath($storedFile);
    }
}
