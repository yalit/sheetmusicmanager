<?php

namespace App\Admin\Action;

use App\Entity\Setlist\Setlist;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class GenerateSetlistPdfAction
{
    public static function new(): Action
    {
        return Action::new('generatePdf', 'Export PDF')
            ->setIcon('fa fa-file-pdf')
            ->renderAsLink()
            ->linkToRoute('admin_generate_setlist_pdf', fn(Setlist $s) => ['id' => $s->getId()]);
    }
}
