<?php

namespace App\Controller\Action;

use App\Entity\Setlist\Setlist;
use App\Storage\StoredFileStorage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

final class MergeSetlistSheetsPdfController extends AbstractController
{
    public function __construct(
        private readonly GotenbergPdfInterface $gotenberg,
        private readonly StoredFileStorage     $storage,
    ) {}

    #[AdminRoute('/setlist/{id}/merge-sheets-pdf', name: 'merge_setlist_sheets_pdf')]
    public function __invoke(Setlist $setlist): Response
    {
        $paths = [];

        foreach ($setlist->getItems() as $item) {
            foreach ($item->getSheet()?->getFiles() as $storedFile) {
                $path = $this->storage->absolutePath($storedFile);
                if (is_file($path)) {
                    $paths[] = $path;
                }
            }
        }

        if ($paths === []) {
            $this->addFlash('warning', 'Aucune partition PDF disponible dans cette setlist.');
            return $this->redirectToRoute('admin_setlist_edit', ['entityId' => $setlist->getId()]);
        }

        return $this->gotenberg->merge()
            ->files(...$paths)
            ->fileName(sprintf('partitions-%s', $setlist->getId()), HeaderUtils::DISPOSITION_ATTACHMENT)
            ->generate()
            ->stream();
    }
}
