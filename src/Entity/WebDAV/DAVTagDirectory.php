<?php

namespace App\Entity\WebDAV;

use App\Entity\WebDAV\Factory\DAVSheetFileFactory;
use App\Repository\SheetRepository;
use Sabre\DAV\Collection;

class DAVTagDirectory extends Collection
{
    public const UNTAGGED = '_Untagged';
    public function __construct(
        private readonly ?string               $tag = null,
        private readonly SheetRepository      $sheetRepository,
        private readonly DAVSheetFileFactory $sheetFileFactory,
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

        return array_map(
            fn($sheet) => $this->sheetFileFactory->new($sheet),
            $sheets,
        );
    }
}
