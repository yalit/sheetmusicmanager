<?php

namespace App\Controller\Admin;

use App\Admin\Fields\CollectionTableField;
use App\Entity\Setlist;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

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
            ->setSearchFields(['title'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title'))
            ->add(DateTimeFilter::new('date'))
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->syncItemPositions($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->syncItemPositions($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function syncItemPositions(Setlist $setlist): void
    {
        $position = 1;
        foreach ($setlist->getItem() as $item) {
            $item->setPosition($position++);
        }
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Title');
        yield DateField::new('date', 'Date');
        yield CollectionTableField::new('item', 'Items')
            ->useEntryCrudForm(SetlistItemCrudController::class)
            ->allowAdd()
            ->allowDelete()
        ;
    }
}
