<?php

namespace App\Controller\WebDav;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebDavController extends AbstractController
{
    #[Route('/dav/{path}', name: 'webdav', requirements: ['path' => '.*'], methods: ['GET', 'HEAD', 'OPTIONS', 'PROPFIND', 'PUT', 'DELETE', 'MKCOL', 'COPY', 'MOVE', 'LOCK', 'UNLOCK'])]
    public function handle(): Response
    {
        return new Response('WebDAV — not yet implemented', Response::HTTP_OK);
    }
}
