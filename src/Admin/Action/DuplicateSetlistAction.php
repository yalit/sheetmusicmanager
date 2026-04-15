<?php

namespace App\Admin\Action;

use App\Entity\Setlist\Setlist;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

final class DuplicateSetlistAction
{
    public static function new(): Action
    {
        return Action::new('duplicate', 'Dupliquer')
            ->setIcon('fa fa-copy')
            ->renderAsLink()
            ->linkToRoute('admin_duplicate_setlist', fn(Setlist $setlist) => ['id' => $setlist->getId()])
            ;
    }
}
