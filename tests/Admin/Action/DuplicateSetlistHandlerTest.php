<?php

namespace App\Tests\Admin\Action;

use App\Admin\Action\DuplicateSetlist;
use App\Admin\Action\DuplicateSetlistHandler;
use App\Entity\SetListItem;
use App\Entity\Setlist;
use App\Entity\Sheet;
use App\Repository\SetlistRepository;
use PHPUnit\Framework\TestCase;

final class DuplicateSetlistHandlerTest extends TestCase
{
    public function testItReturnsANewSetlistWithSameTitleAndNotes(): void
    {
        $original = (new Setlist())->setTitle('Concert')->setNotes('Big night');

        $handler = $this->createMock(SetlistRepository::class);
        $handler->expects($this->once())->method('save');
        $result = (new DuplicateSetlistHandler($handler))(new DuplicateSetlist($original));

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

        $repository = $this->createMock(SetlistRepository::class);
        $repository->expects($this->once())->method('save');
        $result = (new DuplicateSetlistHandler($repository))(new DuplicateSetlist($original));

        static::assertCount(1, $result->getItems());
        static::assertNotSame($item, $result->getItems()->first());
        static::assertSame($sheet, $result->getItems()->first()->getSheet());
    }

    public function testItCallsSaveWithFlushOnTheNewSetlist(): void
    {
        $original = (new Setlist())->setTitle('Concert')->setNotes('');

        $repository = $this->createMock(SetlistRepository::class);
        $repository->expects($this->once())->method('save')->with(
            static::isInstanceOf(Setlist::class),
            true
        );

        (new DuplicateSetlistHandler($repository))(new DuplicateSetlist($original));
    }

    public function testItWorksOnEmptySetlist(): void
    {
        $original = (new Setlist())->setTitle('Empty')->setNotes('');

        $repository = $this->createMock(SetlistRepository::class);
        $repository->expects($this->once())->method('save');
        $result = (new DuplicateSetlistHandler($repository))(new DuplicateSetlist($original));

        static::assertCount(0, $result->getItems());
    }
}
