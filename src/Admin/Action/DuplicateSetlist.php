<?php

namespace App\Admin\Action;

use App\Entity\Setlist;

final class DuplicateSetlist
{
    public Setlist $setlist;

    public function __construct(Setlist $setlist)
    {
        $this->setlist = $setlist;
    }
}
