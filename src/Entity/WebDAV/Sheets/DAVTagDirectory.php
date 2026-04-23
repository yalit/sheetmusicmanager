<?php

namespace App\Entity\WebDAV\Sheets;

use App\Entity\Sheet\Sheet;
use App\Entity\WebDAV\Factory\Sheets\DAVSheetFileFactory;
use App\Repository\SheetRepository;
use App\Service\SheetPdfProvider;
use Sabre\DAV\Collection;

class DAVTagDirectory extends Collection
{
    public const UNTAGGED = '_Untagged';
    public function __construct(
        private readonly ?string               $tag = null,
        private readonly SheetRepository      $sheetRepository,
        private readonly DAVSheetFileFactory $sheetFileFactory,
        private readonly SheetPdfProvider $sheetPdfProvider,
    ) {}

    public function getName(): string
    {
        return $this->tag;
    }

    public function getChildren(): array
    {
        $sheets = $this->tag === self::UNTAGGED
            ? $this->sheetRepository->findUntagged()
            : $this->sheetRepository->findByTag($this->tag);

        $existing_sheets = array_filter($sheets, fn(Sheet $sheet) => $this->sheetPdfProvider->hasContent($sheet));

        return array_map(
            fn($sheet) => $this->sheetFileFactory->new($sheet),
            $existing_sheets,
        );
    }
}
