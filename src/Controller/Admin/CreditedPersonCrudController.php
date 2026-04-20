<?php

namespace App\Controller\Admin;

use App\Entity\Sheet\CreditedPerson;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

/**
 * Not registered in the dashboard menu.
 * Used exclusively as the entry form configuration for CollectionTableField
 * via useEntryCrudForm() in SheetCrudController.
 *
 * @extends AbstractCrudController<CreditedPerson>
 */
class CreditedPersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CreditedPerson::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('person', 'Person');
        yield AssociationField::new('personType', 'Type');
    }
}
