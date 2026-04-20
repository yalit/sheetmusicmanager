<?php

namespace App\Entity\WebDAV;

use Sabre\DAV\Collection;

class DAVRootDirectory extends Collection
{
    public function __construct(
        private readonly DAVSheetsRootDirectory $sheetsRoot,
    ) {}

    public function getName(): string
    {
        return '';
    }

    public function getChildren(): array
    {
        return [$this->sheetsRoot];
    }
}
