<?php

namespace App\Entity\WebDAV\Factory\Sheets;

use App\Entity\Sheet\Sheet;
use App\Entity\WebDAV\Sheets\DAVSheetFile;
use App\Service\SheetPdfProvider;
use App\Service\WebDAV\SheetFilenameGenerator;
use App\Service\WebDAV\StringSanitizer;

final readonly class DAVSheetFileFactory
{
    public function __construct(
        private SheetPdfProvider $provider,
        private SheetFilenameGenerator $filenameGenerator,
    ) {}

    public function new(Sheet $sheet): DAVSheetFile
    {
        return new DAVSheetFile($sheet, $this->provider, $this->filenameGenerator);
    }
}
