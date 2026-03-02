<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Enum\MemberRole;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Member::class;
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
