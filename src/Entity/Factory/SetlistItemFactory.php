<?php

namespace App\Entity\Factory;

use App\Entity\SetListItem;

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
