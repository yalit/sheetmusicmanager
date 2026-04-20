<?php

namespace App\Entity\WebDAV\Factory;

use App\Entity\WebDAV\DAVTagDirectory;
use App\Repository\SheetRepository;
use App\Service\SheetPdfProvider;
use App\Service\WebDAV\StringSanitizer;

final readonly class DAVTagDirectoryFactory
{
    public function __construct(
        private SheetRepository      $sheetRepository,
        private DAVSheetFileFactory $sheetFileFactory,
    ) {}

    public function new(?string $tag = null): DAVTagDirectory
    {
        return new DAVTagDirectory($tag, $this->sheetRepository, $this->sheetFileFactory);
    }
}
