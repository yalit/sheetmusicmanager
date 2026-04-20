<?php

namespace App\Entity\Setlist\Factory;

use App\Entity\Setlist\SetListItem;

class SetlistItemFactory
{
    public static function clone(SetListItem $setListItem): SetListItem
    {
        $item = new SetListItem();
        $item->setPosition($setListItem->getPosition());
        $item->setName($setListItem->getName());
        $item->setNotes($setListItem->getNotes());
        $item->setSheet($setListItem->getSheet());

        return $item;
    }
}
