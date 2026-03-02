<?php

namespace App\Controller\Admin;

use App\Entity\SetListItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Not registered in the dashboard menu.
 * Used exclusively as the entry form configuration for CollectionTableField
 * via useEntryCrudForm() in SetlistCrudController.
 *
 * @extends AbstractCrudController<SetListItem>
 */
class SetlistItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SetListItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Name');
        yield AssociationField::new('sheet', 'Sheet');
    }
}
