<?php

namespace App\Entity\Setlist\Factory;

use App\Entity\Setlist\Setlist;

class SetlistFactory
{
    public static function clone(Setlist $setlist): Setlist
    {
        $newSetlist  = new Setlist();
        $newSetlist->setNotes($setlist->getNotes());
        $newSetlist->setDate(new \DateTime());
        $newSetlist->setTitle($setlist->getTitle());

        foreach ($setlist->getItems() as $item) {
            $newItem = SetlistItemFactory::clone($item);
            $newSetlist->addItem($newItem);
        }
        return $newSetlist;
    }
}
