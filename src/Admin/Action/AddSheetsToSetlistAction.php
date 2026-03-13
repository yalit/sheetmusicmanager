<?php

namespace App\Admin\Action;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class AddSheetsToSetlistAction
{
    public static function new(): Action
    {
        return Action::new('addToSetlist', 'Ajouter à une setlist')
            ->addCssClass('btn btn-primary')
            ->linkToRoute('admin_add_to_setlist')
        ;
    }
}
