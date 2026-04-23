<?php

namespace App\Controller\Admin;

use App\Admin\Action\DuplicateSetlistAction;
use App\Admin\Action\ExportSetlistToForScoreAction;
use App\Admin\Action\GenerateSetlistPdfAction;
use App\Admin\Action\MergeSetlistSheetsPdfAction;
use App\Admin\Fields\CollectionTableField;
use App\Entity\Setlist\Setlist;
use App\Security\Voter\SetlistVoter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['title'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, DuplicateSetlistAction::new())
            ->add(Crud::PAGE_INDEX, GenerateSetlistPdfAction::new())
            ->add(Crud::PAGE_DETAIL, GenerateSetlistPdfAction::new())
            ->add(Crud::PAGE_INDEX, MergeSetlistSheetsPdfAction::new())
            ->add(Crud::PAGE_DETAIL, MergeSetlistSheetsPdfAction::new())
            ->add(Crud::PAGE_INDEX, ExportSetlistToForScoreAction::new())
            ->add(Crud::PAGE_DETAIL, ExportSetlistToForScoreAction::new())
            ->setPermission(Action::INDEX,        SetlistVoter::INDEX)
            ->setPermission(Action::DETAIL,       SetlistVoter::DETAIL)
            ->setPermission(Action::NEW,          SetlistVoter::NEW)
            ->setPermission(Action::EDIT,         SetlistVoter::EDIT)
            ->setPermission(Action::DELETE,       SetlistVoter::DELETE)
            ->setPermission(ExportSetlistToForScoreAction::NAME,     SetlistVoter::EXPORT_FORSCORE)
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
        foreach ($setlist->getItems() as $item) {
            $item->setPosition($position++);
        }
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Title');
        yield DateField::new('date', 'Date');
        yield CollectionTableField::new('items', 'Items')
            ->useEntryCrudForm(SetlistItemCrudController::class)
            ->allowAdd()
            ->allowDelete()
            ->setFormTypeOption('allow_sort', true)
        ;
    }
}
