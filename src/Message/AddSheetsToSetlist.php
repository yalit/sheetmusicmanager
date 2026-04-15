<?php

namespace App\Message;

use App\Entity\Setlist\Setlist;
use App\Entity\Sheet\Sheet;
use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\Validator\Constraints\NotNull;

#[AsMessage]
class AddSheetsToSetlist
{
    #[NotNull]
    public Setlist $setlist;
    /**
     * @var Sheet[]
     */
    public array $sheets = [];
}
