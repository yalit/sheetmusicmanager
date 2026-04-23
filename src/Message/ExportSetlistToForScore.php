<?php

namespace App\Message;

use App\Entity\Setlist\Setlist;

final class ExportSetlistToForScore
{
    public function __construct(
        public readonly Setlist $setlist,
    ) {}
}
