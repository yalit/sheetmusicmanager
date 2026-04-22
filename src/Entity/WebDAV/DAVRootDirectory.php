<?php

namespace App\Entity\WebDAV;

use App\Entity\WebDAV\Setlist\DAVSetlistsRootDirectory;
use App\Entity\WebDAV\Sheets\DAVSheetsRootDirectory;
use Sabre\DAV\Collection;

class DAVRootDirectory extends Collection
{
    public function __construct(
        private readonly DAVSheetsRootDirectory    $sheetsRoot,
        private readonly DAVSetlistsRootDirectory  $setlistsRoot,
    ) {}

    public function getName(): string
    {
        return '';
    }

    public function getChildren(): array
    {
        return [$this->sheetsRoot, $this->setlistsRoot];
    }
}
