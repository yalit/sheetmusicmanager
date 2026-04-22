<?php

namespace App\Entity\WebDAV\Setlist;

use App\Entity\Setlist\Setlist;
use App\Entity\Setlist\SetListItem;
use App\Entity\WebDAV\Factory\Setlist\DAVSetlistFileFactory;
use App\Service\WebDAV\StringSanitizer;
use Sabre\DAV\Collection;

class DAVSetlistDirectory extends Collection
{
    public function __construct(
        private readonly Setlist               $setlist,
        private readonly DAVSetlistFileFactory $fileFactory,
        private readonly StringSanitizer       $sanitizer,
    ) {}

    public function getName(): string
    {
        return $this->sanitizer->sanitize($this->setlist->getTitle());
    }

    public function getChildren(): array
    {
        return array_map(
            fn(SetListItem $item) => $this->fileFactory->new($item),
            $this->setlist->getItems()->toArray(),
        );
    }
}
