<?php

namespace App\Admin\Action;

use App\Entity\Setlist\Setlist;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

final class ExportSetlistToForScoreAction
{
    public const NAME = 'exportToForScore';
    public static function new(): Action
    {
        return Action::new(ExportSetlistToForScoreAction::NAME, 'Export to forScore')
            ->setIcon('fa fa-music')
            ->renderAsLink()
            ->linkToRoute('admin_export_setlist_forscore', fn(Setlist $s) => ['id' => $s->getId()]);
    }
}
