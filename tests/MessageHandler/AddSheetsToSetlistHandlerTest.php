<?php

namespace App\Tests\MessageHandler;

use App\Entity\Setlist;
use App\Entity\SetListItem;
use App\Entity\Sheet;
use App\Message\AddSheetsToSetlist;
use App\MessageHandler\AddSheetsToSetlistHandler;
use App\Repository\SetlistRepository;
use PHPUnit\Framework\TestCase;

final class AddSheetsToSetlistHandlerTest extends TestCase
{
    private function makeHandlerWithExpectedSave(Setlist $setlist): AddSheetsToSetlistHandler
    {
        $repository = $this->createMock(SetlistRepository::class);
        $repository->expects($this->once())->method('save')->with($setlist, true);

        return new AddSheetsToSetlistHandler($repository);
    }

    public function testItAddsSheetsToEmptySetlist(): void
    {
        $setlist = (new Setlist())->setTitle('Test');
        $sheet1  = (new Sheet())->setTitle('Sheet A');
        $sheet2  = (new Sheet())->setTitle('Sheet B');

        $message          = new AddSheetsToSetlist();
        $message->setlist = $setlist;
        $message->sheets  = [$sheet1, $sheet2];

        $this->makeHandlerWithExpectedSave($setlist)($message);

        $items = $setlist->getItems()->toArray();
        static::assertCount(2, $items);

        static::assertSame($sheet1, $items[0]->getSheet());
        static::assertSame(1, $items[0]->getPosition());

        static::assertSame($sheet2, $items[1]->getSheet());
        static::assertSame(2, $items[1]->getPosition());
    }

    public function testItAppendsSheetsAfterExistingItems(): void
    {
        $setlist = (new Setlist())->setTitle('Test');

        $existingItem = (new SetListItem())->setPosition(1)->setName('')->setNotes('');
        $setlist->addItem($existingItem);

        $newSheet         = (new Sheet())->setTitle('New Sheet');
        $message          = new AddSheetsToSetlist();
        $message->setlist = $setlist;
        $message->sheets  = [$newSheet];

        $this->makeHandlerWithExpectedSave($setlist)($message);

        $items = $setlist->getItems()->toArray();
        static::assertCount(2, $items);
        static::assertSame($newSheet, $items[1]->getSheet());
        static::assertSame(2, $items[1]->getPosition());
    }

    public function testItCallsSaveEvenWithNoSheets(): void
    {
        $setlist = (new Setlist())->setTitle('Test');

        $message          = new AddSheetsToSetlist();
        $message->setlist = $setlist;
        $message->sheets  = [];

        $this->makeHandlerWithExpectedSave($setlist)($message);

        static::assertCount(0, $setlist->getItems());
    }
}
