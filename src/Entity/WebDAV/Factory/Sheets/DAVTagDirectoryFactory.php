<?php

namespace App\Entity\WebDAV\Factory\Sheets;

use App\Entity\WebDAV\Sheets\DAVTagDirectory;
use App\Repository\SheetRepository;
use App\Service\SheetPdfProvider;

final readonly class DAVTagDirectoryFactory
{
    public function __construct(
        private SheetRepository      $sheetRepository,
        private DAVSheetFileFactory $sheetFileFactory,
        private SheetPdfProvider    $sheetPdfProvider,
    ) {}

    public function new(?string $tag = null): DAVTagDirectory
    {
        return new DAVTagDirectory($tag, $this->sheetRepository, $this->sheetFileFactory, $this->sheetPdfProvider);
    }
}
