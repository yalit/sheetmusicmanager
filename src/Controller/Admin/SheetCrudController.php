<?php

namespace App\Controller\Admin;

use App\Admin\Action\AddSheetsToSetlistAction;
use App\Admin\Action\ExportCsvAction;
use App\Admin\Fields\ChoiceAutoCompleteStringField;
use App\Admin\Fields\CollectionTableField;
use App\Admin\Fields\PDFField;
use App\Entity\Sheet;
use App\Entity\ValueObject\StoredFile;
use App\Export\CsvExporter;
use App\Filter\HasPdfFilter;
use App\Repository\SheetRepository;
use App\Security\Voter\SheetVoter;
use App\Storage\StoredFileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterConfigDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @extends AbstractCrudController<Sheet>
 */
class SheetCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SheetRepository   $sheetRepository,
        private readonly RequestStack      $requestStack,
        private readonly StoredFileStorage $storage,
        private readonly CsvExporter       $csvExporter,
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
            ->add(Crud::PAGE_INDEX, ExportCsvAction::new())
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
            ->setExistingFiles($this->getExistingFilesData())
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
     * @param AdminContext<Sheet> $context
     */
    #[AdminRoute(path: '/export', name: 'export', options: ['methods' => ['GET']])]
    public function export(AdminContext $context): StreamedResponse
    {
        /** @var Sheet[] $sheets */
        $sheets = $this->getIndexQueryBuilder($context)->getQuery()->getResult();

        $rows = array_map(fn (Sheet $sheet) => [
            $sheet->getId(),
            $sheet->getTitle(),
            implode(', ', $sheet->getRefs()),
            implode(', ', $sheet->getTags()),
            implode(' / ', $sheet->getCredit()->map(fn ($c) => (string) $c)->toArray()),
            count($sheet->getFiles()),
            $sheet->getCreatedAt()?->format('Y-m-d'),
        ], $sheets);

        return $this->csvExporter->export(
            sprintf('sheets-%s.csv', date('Y-m-d')),
            ['ID', 'Title', 'Refs', 'Tags', 'Credits', 'Files', 'Created at'],
            $rows
        );
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
        if (!$request) {
            return;
        }
        $removedData = $request->request->get('app_pdf_field_removed', '[]') ?: '[]';

        if (!is_string($removedData)) { return; }
        /** @var array<array<string,string>> $removed */
        $removed = json_decode($removedData, true);
        foreach ($removed as $data) {
            $this->storage->delete(StoredFile::fromArray($data));
        }

        $keptData = $request->request->get('app_pdf_field_kept', '[]') ?: '[]';
        if (!is_string($keptData)) {return;}

        /** @var array<array<string,string>> $kept */
        $kept = json_decode($keptData, true);

        $newFilenames = $this->moveUploadedFiles($entityInstance);
        $entityInstance->setFiles(array_merge(array_map(fn($arr) => StoredFile::fromArray($arr), $kept), $newFilenames));

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @return StoredFile[]
     */
    private function moveUploadedFiles(Sheet $entityInstance): array
    {
        $filenames = [];
        foreach ($entityInstance->getUploadedFiles() as $file) {
            $filenames[] = $this->storage->save($file);
        }
        $entityInstance->setUploadedFiles([]);
        return $filenames;
    }

    /**
     * @return StoredFile[]
     */
    private function getExistingFilesData(): array
    {
        try {
            $entity = $this->getContext()?->getEntity()?->getInstance();
        } catch (Exception $e) {
            return [];
        }

        if (!$entity instanceof Sheet) {
            return [];
        }

        return $entity->getFiles();
    }

    /**
     * @param AdminContext<Sheet> $context
     */
    private function getIndexQueryBuilder(AdminContext $context): QueryBuilder
    {
        $entityDto = $this->container->get(EntityFactory::class)->create(static::getEntityFqcn());
        $fields    = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters   = $this->container->get(FilterFactory::class)->create(
            $context->getCrud()?->getFiltersConfig() ?? new FilterConfigDto(),
            $fields,
            $entityDto
        );

        return $this->createIndexQueryBuilder(
            $context->getSearch() ?? new SearchDto($context->getRequest(), null, null, [], [], null),
            $entityDto,
            $fields,
            $filters
        );
    }

}
