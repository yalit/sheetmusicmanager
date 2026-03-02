<?php

namespace App\Controller\Admin;

use App\Admin\Type\SetListItemType;
use App\Entity\Setlist;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Setlist>
 */
class SetlistCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Setlist::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Setlist')
            ->setEntityLabelInPlural('Setlists')
            ->setDefaultSort(['date' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Title');
        yield DateField::new('date', 'Date');
        yield CollectionField::new('item', 'Items')
            ->setEntryType(SetListItemType::class)
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex()
        ;
    }
}
