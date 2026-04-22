<?php

namespace App\Entity\WebDAV\Factory\Setlist;

use App\Entity\Setlist\SetListItem;
use App\Entity\WebDAV\Setlist\DAVSetlistFile;
use App\Service\SheetPdfProvider;
use App\Service\WebDAV\SetlistFilenameGenerator;

final readonly class DAVSetlistFileFactory
{
    public function __construct(
        private SheetPdfProvider          $provider,
        private SetlistFilenameGenerator $filenameGenerator,
    ) {}

    public function new(SetListItem $item): DAVSetlistFile
    {
        return new DAVSetlistFile($item, $this->provider, $this->filenameGenerator);
    }
}
