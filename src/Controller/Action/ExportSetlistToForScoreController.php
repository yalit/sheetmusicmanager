<?php

namespace App\Controller\Action;

use App\Entity\Setlist\Setlist;
use App\Message\ExportSetlistToForScore;
use App\MessageHandler\ExportSetlistToForScoreHandler;
use App\Security\Voter\SetlistVoter;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ExportSetlistToForScoreController extends AbstractController
{
    #[AdminRoute('/setlist/{id}/export/forscore', name: 'export_setlist_forscore')]
    public function __invoke(Setlist $setlist, ExportSetlistToForScoreHandler $handler): Response
    {
        $this->denyAccessUnlessGranted(SetlistVoter::EXPORT_FORSCORE, $setlist);

        $xml = $handler(new ExportSetlistToForScore($setlist));

        return new Response($xml, Response::HTTP_OK, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => sprintf('attachment; filename="%s.4ss"', $setlist->getTitle()),
        ]);
    }
}
