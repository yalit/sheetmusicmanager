<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Enum\MemberRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

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
