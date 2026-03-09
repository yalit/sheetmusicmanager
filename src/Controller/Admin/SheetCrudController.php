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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractCrudController<Sheet>
 */
class SheetCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SheetRepository $sheetRepository,
        private readonly RequestStack    $requestStack,
        private readonly Filesystem      $filesystem,
        private readonly string          $projectDir,
        private readonly string          $uploadDir,
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
            $this->filesystem->remove(
                $this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir . DIRECTORY_SEPARATOR . $filename
            );
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
            $filename = $file->getClientOriginalName();
            $file->move($this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir, $filename);
            $filenames[] = $filename;
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

        $webPrefix = '/' . preg_replace('#^public/#', '', $this->uploadDir);
        $data = [];
        foreach ($entity->getFiles() as $filename) {
            $fullPath = $this->projectDir . DIRECTORY_SEPARATOR . $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            $size = is_file($fullPath) ? filesize($fullPath) : 0;
            $data[] = [
                'name' => $filename,
                'size' => $this->formatFileSize($size),
                'web_path' => $webPrefix . '/' . $filename,
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
