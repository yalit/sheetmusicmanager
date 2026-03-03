<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Enum\MemberRole;
use App\Security\Voter\MemberVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<Member>
 */
class MemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Member::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Member')
            ->setEntityLabelInPlural('Members')
            ->setSearchFields(['name', 'email'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::INDEX,  MemberVoter::INDEX)
            ->setPermission(Action::DETAIL, MemberVoter::DETAIL)
            ->setPermission(Action::NEW,    MemberVoter::NEW)
            ->setPermission(Action::EDIT,   MemberVoter::EDIT)
            ->setPermission(Action::DELETE, MemberVoter::DELETE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name'))
            ->add(TextFilter::new('email'))
            ->add(ChoiceFilter::new('role')->setChoices(MemberRole::choices()))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new("name");
        yield TextField::new('email');
        yield TextField::new('plainPassword', "Password")
            ->onlyOnForms()
            ->setRequired($pageName === "new")
            ->setHelp($pageName === "edit" ? "Remplir en cas de changement..." : "")
        ;
        yield ChoiceField::new('role', 'Role')
            ->setChoices(MemberRole::choices())
            ->setFormTypeOptions(['choice_value' => fn(?MemberRole $r) => $r?->value])
            ->setRequired(false)
        ;
    }
}
