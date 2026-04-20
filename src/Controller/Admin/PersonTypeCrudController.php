<?php

namespace App\Controller\Admin;

use App\Entity\Sheet\PersonType;
use App\Security\Voter\PersonTypeVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<PersonType>
 */
class PersonTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PersonType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Person Type')
            ->setEntityLabelInPlural('Person Types')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::INDEX,  PersonTypeVoter::INDEX)
            ->setPermission(Action::DETAIL, PersonTypeVoter::DETAIL)
            ->setPermission(Action::NEW,    PersonTypeVoter::NEW)
            ->setPermission(Action::EDIT,   PersonTypeVoter::EDIT)
            ->setPermission(Action::DELETE, PersonTypeVoter::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Name');
    }
}
