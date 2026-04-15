<?php

namespace App\Admin\Action;

use App\Entity\Setlist\Factory\SetlistFactory;
use App\Entity\Setlist\Setlist;
use App\Repository\SetlistRepository;

final readonly class DuplicateSetlistHandler
{
    public function __construct(private SetlistRepository $setlistRepository)
    {
    }

    public function __invoke(DuplicateSetlist $duplicateSetlist): Setlist
    {
        $newSetlist = SetlistFactory::clone($duplicateSetlist->setlist);
        $this->setlistRepository->save($newSetlist, true);
        return $newSetlist;
    }
}
