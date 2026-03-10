<?php

namespace App\Controller\Admin;

use App\Admin\Action\AddSheetsToSetlistAction;
use App\Admin\Fields\ChoiceAutoCompleteStringField;
use App\Admin\Fields\CollectionTableField;
use App\Admin\Fields\PDFField;
use App\Entity\Sheet;
use App\Filter\HasPdfFilter;
use App\Repository\SheetRepository;
use App\Security\Voter\SheetVoter;
use App\Storage\SheetFileStorage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractCrudController<Sheet>
 */
class SheetCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SheetRepository  $sheetRepository,
        private readonly RequestStack     $requestStack,
        private readonly SheetFileStorage $storage,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Sheet::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Sheet')
            ->setEntityLabelInPlural('Sheets')
            ->setSearchFields(['title', 'tags'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->addBatchAction(AddSheetsToSetlistAction::new())
            ->setPermission(Action::INDEX,  SheetVoter::INDEX)
            ->setPermission(Action::DETAIL, SheetVoter::DETAIL)
            ->setPermission(Action::NEW,    SheetVoter::NEW)
            ->setPermission(Action::EDIT,   SheetVoter::EDIT)
            ->setPermission(Action::DELETE, SheetVoter::DELETE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title'))
            ->add(HasPdfFilter::new('files', 'Has PDF'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield FormField::addColumn(8);
        yield FormField::addFieldset("General");
        yield TextField::new('title', 'Titre')->setColumns(8);

        yield PDFField::new('files', 'Fichier PDF')->hideOnForm();
        yield PDFField::new('uploadedFiles', 'Fichier PDF')
            ->onlyOnForms()
            ->setExistingFiles($this->buildExistingFilesData())
            ->setRequired(false);

        yield FormField::addColumn(4);
        yield FormField::addFieldset("Details");
        yield ChoiceAutoCompleteStringField::new('refs')
            ->setChoices([$this->sheetRepository, 'getAllRefs']);
        yield ChoiceAutoCompleteStringField::new('tags')
            ->setChoices([$this->sheetRepository, 'getAllTags']);

        yield CollectionTableField::new('credit', 'Credits')
            ->useEntryCrudForm(CreditedPersonCrudController::class)
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex();
        yield TextareaField::new('notes')
            ->setNumOfRows(5)
            ->hideOnIndex();
    }

    /**
     * @param Sheet $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $newFilenames = $this->moveUploadedFiles($entityInstance);
        $entityInstance->setFiles($newFilenames);
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param Sheet $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $removed = json_decode($request?->request->get('app_pdf_field_removed', '[]') ?: '[]', true);
        foreach ($removed as $filename) {
            $this->storage->delete($filename);
        }

        $kept = json_decode($request?->request->get('app_pdf_field_kept', '[]') ?: '[]', true);
        $newFilenames = $this->moveUploadedFiles($entityInstance);
        $entityInstance->setFiles(array_merge($kept, $newFilenames));

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function moveUploadedFiles(Sheet $entityInstance): array
    {
        $filenames = [];
        foreach ($entityInstance->getUploadedFiles() as $file) {
            $filenames[] = $this->storage->save($file);
        }
        $entityInstance->setUploadedFiles([]);
        return $filenames;
    }

    private function buildExistingFilesData(): array
    {
        $entity = $this->getContext()?->getEntity()?->getInstance();
        if (!$entity instanceof Sheet) {
            return [];
        }

        $data = [];
        foreach ($entity->getFiles() as $filename) {
            $fullPath = $this->storage->absolutePath($filename);
            $size = is_file($fullPath) ? filesize($fullPath) : 0;
            $data[] = [
                'name' => $filename,
                'size' => $this->formatFileSize($size),
                'web_path' => $this->storage->webPath($filename),
            ];
        }
        return $data;
    }

    private function formatFileSize(int|false $bytes): string
    {
        if ($bytes === false || $bytes < 1024) {
            return ($bytes ?: 0) . ' o';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' Ko';
        }
        return round($bytes / (1024 * 1024), 1) . ' Mo';
    }
}
