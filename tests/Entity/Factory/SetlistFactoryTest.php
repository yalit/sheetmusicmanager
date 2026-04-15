<?php

namespace App\Tests\Entity\Factory;

use App\Entity\Setlist\Factory\SetlistFactory;
use App\Entity\Setlist\Setlist;
use App\Entity\Setlist\SetListItem;
use App\Entity\Sheet\Sheet;
use PHPUnit\Framework\TestCase;

final class SetlistFactoryTest extends TestCase
{
    private function makeSetlist(): Setlist
    {
        $setlist = (new Setlist())->setTitle('Concert')->setNotes('Annual gig');

        $item = (new SetListItem())
            ->setPosition(1)
            ->setName('Opener')
            ->setNotes('')
            ->setSheet((new Sheet())->setTitle('Sheet A'));

        $setlist->addItem($item);

        return $setlist;
    }

    public function testCloneCopiesTitleAndNotes(): void
    {
        $original = $this->makeSetlist();

        $clone = SetlistFactory::clone($original);

        static::assertSame($original->getTitle(), $clone->getTitle());
        static::assertSame($original->getNotes(), $clone->getNotes());
    }

    public function testCloneSetsDateToToday(): void
    {
        $clone = SetlistFactory::clone($this->makeSetlist());

        static::assertSame((new \DateTime())->format('Y-m-d'), $clone->getDate()->format('Y-m-d'));
    }

    public function testCloneClonesAllItems(): void
    {
        $original = $this->makeSetlist();

        $clone = SetlistFactory::clone($original);

        static::assertCount(1, $clone->getItems());

        $originalItem = $original->getItems()->first();
        $clonedItem   = $clone->getItems()->first();

        static::assertNotSame($originalItem, $clonedItem);
        static::assertSame($originalItem->getPosition(), $clonedItem->getPosition());
        static::assertSame($originalItem->getSheet(), $clonedItem->getSheet());
    }

    public function testCloneIsDistinctObject(): void
    {
        $original = $this->makeSetlist();

        static::assertNotSame($original, SetlistFactory::clone($original));
    }

    public function testCloneOfEmptySetlistHasNoItems(): void
    {
        $original = (new Setlist())->setTitle('Empty')->setNotes('');

        $clone = SetlistFactory::clone($original);

        static::assertCount(0, $clone->getItems());
        static::assertNotNull($clone->getDate());
    }

    public function testOriginalIsNotMutated(): void
    {
        $original      = $this->makeSetlist();
        $originalTitle = $original->getTitle();
        $originalCount = $original->getItems()->count();

        SetlistFactory::clone($original);

        static::assertSame($originalTitle, $original->getTitle());
        static::assertCount($originalCount, $original->getItems());
    }
}
