<?php

namespace App\Entity\WebDAV\Factory\Setlist;

use App\Entity\Setlist\Setlist;
use App\Entity\WebDAV\Setlist\DAVSetlistDirectory;
use App\Service\WebDAV\StringSanitizer;

final readonly class DAVSetlistDirectoryFactory
{
    public function __construct(
        private DAVSetlistFileFactory $fileFactory,
        private StringSanitizer       $sanitizer,
    ) {}

    public function new(Setlist $setlist): DAVSetlistDirectory
    {
        return new DAVSetlistDirectory($setlist, $this->fileFactory, $this->sanitizer);
    }
}
