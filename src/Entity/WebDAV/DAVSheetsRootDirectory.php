<?php

namespace App\Entity\WebDAV;

use App\Entity\WebDAV\Factory\DAVTagDirectoryFactory;
use App\Repository\SheetRepository;
use Sabre\DAV\Collection;

class DAVSheetsRootDirectory extends Collection
{
    public function __construct(
        private readonly SheetRepository      $sheetRepository,
        private readonly DAVTagDirectoryFactory $tagDirectoryFactory
    ) {}

    public function getName(): string
    {
        return 'sheets';
    }

    public function getChildren(): array
    {
        $directories = array_map(
            fn(string $tag) => $this->tagDirectoryFactory->new($tag),
            $this->sheetRepository->getAllTags(),
        );

        if ($this->sheetRepository->findUntagged() !== []) {
            $directories[] = $this->tagDirectoryFactory->new(DAVTagDirectory::UNTAGGED);
        }

        return $directories;
    }
}
