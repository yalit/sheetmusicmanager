<?php

namespace App\Controller\Action;

use App\Form\AddSheetsToSetlistType;
use App\Message\Factory\AddSheetsToSetListFactory;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class AddToSetlistController extends AbstractController
{
    public function __construct(
        private readonly AddSheetsToSetListFactory $factory,
        private readonly MessageBusInterface $messageBus,
    )
    {
    }

    #[AdminRoute('/sheet/add-to-setlist', name: 'add_to_setlist', options: ['methods' => ['POST']])]
    public function addToSetlistRequest(Request $request): Response
    {
        // EasyAdmin batch action: entity IDs come as batchActionEntityIds[] in the first POST
        /** @var string[] $sheetIds */
        $sheetIds = $request->request->all('batchActionEntityIds') ?: [];

        $addToSetlist = $this->factory->create($sheetIds);
        $form = $this->createForm(AddSheetsToSetlistType::class, $addToSetlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageBus->dispatch($addToSetlist);

            return $this->redirectToRoute('admin_setlist_edit', ['entityId' => $addToSetlist->setlist->getId()]);
        }

        return $this->render('admin/action/add_to_setlist.html.twig', [
            'form'         => $form,
            'sheet_count'  => count($sheetIds),
        ]);
    }

}
