<?php

namespace App\Entity\WebDAV\Setlist;

use App\Entity\Setlist\Setlist;
use App\Entity\WebDAV\Factory\Setlist\DAVSetlistDirectoryFactory;
use App\Repository\SetlistRepository;
use Sabre\DAV\Collection;

class DAVSetlistsRootDirectory extends Collection
{
    public function __construct(
        private readonly SetlistRepository          $setlistRepository,
        private readonly DAVSetlistDirectoryFactory $directoryFactory,
    ) {}

    public function getName(): string
    {
        return 'setlists';
    }

    public function getChildren(): array
    {
        return array_map(
            fn(Setlist $setlist) => $this->directoryFactory->new($setlist),
            $this->setlistRepository->findAll(),
        );
    }
}
