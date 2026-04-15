<?php

namespace App\Controller\Action;

use App\Entity\Setlist\Setlist;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GenerateSetlistPdfController extends AbstractController
{
    public function __construct(private readonly GotenbergPdfInterface $gotenberg) {}

    #[AdminRoute('/setlist/{id}/pdf', name: 'generate_setlist_pdf')]
    public function __invoke(Setlist $setlist): StreamedResponse
    {
        return $this->gotenberg->html()
            ->content('admin/pdf/setlist.html.twig', ['setlist' => $setlist])
            ->fileName(sprintf('setlist-%s', $setlist->getId()), HeaderUtils::DISPOSITION_ATTACHMENT)
            ->generate()
            ->stream();
    }
}
