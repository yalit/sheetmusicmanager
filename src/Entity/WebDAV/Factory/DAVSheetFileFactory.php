<?php

namespace App\Entity\WebDAV\Factory;

use App\Entity\Sheet\Sheet;
use App\Entity\WebDAV\DAVSheetFile;
use App\Service\SheetPdfProvider;
use App\Service\WebDAV\StringSanitizer;

final readonly class DAVSheetFileFactory
{
    public function __construct(
        private SheetPdfProvider $provider,
        private StringSanitizer  $stringSanitizer,
    ) {}

    public function new(Sheet $sheet): DAVSheetFile
    {
        return new DAVSheetFile($sheet, $this->provider, $this->stringSanitizer);
    }
}
