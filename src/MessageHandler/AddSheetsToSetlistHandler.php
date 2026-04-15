<?php

namespace App\MessageHandler;

use App\Entity\Setlist\SetListItem;
use App\Message\AddSheetsToSetlist;
use App\Repository\SetlistRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AddSheetsToSetlistHandler
{
    public function __construct(
        private SetlistRepository $setlistRepository,
    ) {}

    public function __invoke(AddSheetsToSetlist $message): void
    {
        $setlist = $message->setlist;
        $sheets = $message->sheets;

        $maxPosition = count($setlist->getItems());

        foreach ($sheets as $sheet) {
            $item = new SetListItem();
            $item->setSheet($sheet);
            $item->setPosition(++$maxPosition);
            $item->setName('');
            $item->setNotes('');
            $setlist->addItem($item);
        }
        $this->setlistRepository->save($setlist, flush: true);
    }
}
