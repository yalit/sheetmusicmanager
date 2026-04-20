<?php

namespace App\Tests\Entity\Factory;

use App\Entity\Setlist\Factory\SetlistItemFactory;
use App\Entity\Setlist\SetListItem;
use App\Entity\Sheet\Sheet;
use PHPUnit\Framework\TestCase;

final class SetlistItemFactoryTest extends TestCase
{
    private function makeItem(): SetListItem
    {
        $sheet = (new Sheet())->setTitle('Sheet A');

        return (new SetListItem())
            ->setPosition(3)
            ->setName('Intro')
            ->setNotes('Play softly')
            ->setSheet($sheet);
    }

    public function testCloneCopiesAllFields(): void
    {
        $original = $this->makeItem();

        $clone = SetlistItemFactory::clone($original);

        static::assertSame($original->getPosition(), $clone->getPosition());
        static::assertSame($original->getName(), $clone->getName());
        static::assertSame($original->getNotes(), $clone->getNotes());
        static::assertSame($original->getSheet(), $clone->getSheet());
    }

    public function testCloneIsDistinctObject(): void
    {
        $original = $this->makeItem();

        $clone = SetlistItemFactory::clone($original);

        static::assertNotSame($original, $clone);
    }
}
