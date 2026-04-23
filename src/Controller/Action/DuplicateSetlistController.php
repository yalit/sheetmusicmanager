<?php

namespace App\Controller\Action;

use App\Entity\Setlist\Setlist;
use App\Message\DuplicateSetlist;
use App\MessageHandler\DuplicateSetlistHandler;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class DuplicateSetlistController extends AbstractController
{

    #[AdminRoute('/setlist/{id}/duplicate', 'duplicate_setlist')]
    public function duplicate(Setlist $setlist, DuplicateSetlistHandler $handler): Response
    {
        $newSetlist = $handler(new DuplicateSetlist($setlist));
        $this->addFlash('success', sprintf('Setlist "%s" dupliquée.', $setlist->getTitle()));
        return $this->redirectToRoute('admin_setlist_edit', ['entityId' => $newSetlist->getId()]);
    }
}
