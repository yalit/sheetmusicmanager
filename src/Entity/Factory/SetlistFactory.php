<?php

namespace App\Entity\Factory;

use App\Entity\Setlist;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

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
