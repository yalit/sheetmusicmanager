<?php

namespace App\Tests\Admin\Action;

use App\Entity\Setlist\Setlist;
use App\Entity\Setlist\SetListItem;
use App\Entity\Sheet\Sheet;
use App\Message\DuplicateSetlist;
use App\MessageHandler\DuplicateSetlistHandler;
use App\Repository\SetlistRepository;
use PHPUnit\Framework\TestCase;

final class DuplicateSetlistHandlerTest extends TestCase
{
    private function makeMockRepository(): DuplicateSetlistHandler
    {
        $repository = $this->createMock(SetlistRepository::class);
        $repository->expects($this->once())->method('save')->with(
            static::isInstanceOf(Setlist::class),
            true
        );

        return new DuplicateSetlistHandler($repository);
    }

    public function testItReturnsANewSetlistWithSameTitleAndNotes(): void
    {
        $original = (new Setlist())->setTitle('Concert')->setNotes('Big night');

        $result = $this->makeMockRepository()(new DuplicateSetlist($original));

        static::assertNotSame($original, $result);
        static::assertSame('Concert', $result->getTitle());
        static::assertSame('Big night', $result->getNotes());
    }

    public function testItClonesAllItems(): void
    {
        $sheet    = (new Sheet())->setTitle('Sheet A');
        $original = (new Setlist())->setTitle('Concert')->setNotes('');
        $item     = (new SetListItem())->setPosition(1)->setName('Opener')->setNotes('')->setSheet($sheet);
        $original->addItem($item);

        $result = $this->makeMockRepository()(new DuplicateSetlist($original));

        static::assertCount(1, $result->getItems());
        static::assertNotSame($item, $result->getItems()->first());
        static::assertSame($sheet, $result->getItems()->first()->getSheet());
    }

    public function testItCallsSaveWithFlushOnTheNewSetlist(): void
    {
        $this->makeMockRepository()(new DuplicateSetlist((new Setlist())->setTitle('Concert')->setNotes('')));
    }

    public function testItWorksOnEmptySetlist(): void
    {
        $result = $this->makeMockRepository()(new DuplicateSetlist((new Setlist())->setTitle('Empty')->setNotes('')));

        static::assertCount(0, $result->getItems());
    }
}
