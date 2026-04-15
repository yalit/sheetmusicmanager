<?php

namespace App\Admin\Action;

use App\Entity\Setlist\Setlist;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class MergeSetlistSheetsPdfAction
{
    public static function new(): Action
    {
        return Action::new('mergeSheetsPdf', 'Export partitions')
            ->setIcon('fa fa-file-pdf')
            ->renderAsLink()
            ->linkToRoute('admin_merge_setlist_sheets_pdf', fn(Setlist $s) => ['id' => $s->getId()]);
    }
}
