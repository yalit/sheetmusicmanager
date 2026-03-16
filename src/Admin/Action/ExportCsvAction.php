<?php

namespace App\Admin\Action;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class ExportCsvAction
{
    public static function new(): Action
    {
        return Action::new('export', 'Export CSV')
            ->linkToCrudAction('export')
            ->setIcon('fa fa-download')
            ->addCssClass('btn btn-secondary')
            ->createAsGlobalAction();
    }
}
