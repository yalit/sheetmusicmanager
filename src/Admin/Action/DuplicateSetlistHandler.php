<?php

namespace App\Admin\Action;

use App\Entity\Factory\SetlistFactory;
use App\Entity\Setlist;
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
