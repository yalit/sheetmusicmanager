<?php

namespace App\Service\WebDAV;

use App\Entity\Setlist\SetListItem;
use App\Storage\StoredFileStorage;

/**
 * @template-implements NameGenerator<SetListItem>
 */
final readonly class SetlistFilenameGenerator implements NameGenerator
{
    public function __construct(
        private StringSanitizer    $sanitizer,
        private StoredFileStorage  $storage,
    ) {}

    /**
     * @param SetListItem $data
     */
    public function generate($data): string
    {
        $position = str_pad((string) $data->getPosition(), 2, '0', STR_PAD_LEFT);
        $name     = $this->sanitizer->sanitize($data->getName());
        $suffix   = $this->hasFileOnDisk($data) ? '' : ' - NOFILE -';

        return sprintf('%s - %s%s.pdf', $position, $name, $suffix);
    }

    private function hasFileOnDisk(SetListItem $item): bool
    {
        foreach ($item->getSheet()->getFiles() as $file) {
            if (file_exists($this->storage->absolutePath($file))) {
                return true;
            }
        }

        return false;
    }
}
