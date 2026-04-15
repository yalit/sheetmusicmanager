<?php

namespace App\Controller\WebDav;

use App\Entity\WebDAV\DAVRootDirectory;
use Sabre\DAV\Server;
use Sabre\HTTP\Request as SabreRequest;
use Sabre\HTTP\Response as SabreResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebDavController extends AbstractController
{
    public function __construct(
        private readonly DAVRootDirectory $rootDirectory,
    ) {}

    #[Route('/dav/{path}', name: 'webdav', requirements: ['path' => '.*'], methods: ['GET', 'HEAD', 'OPTIONS', 'PROPFIND', 'PUT', 'DELETE', 'MKCOL', 'COPY', 'MOVE', 'LOCK', 'UNLOCK'])]
    public function handle(Request $request): Response
    {
        $server = new Server($this->rootDirectory);
        $server->setBaseUri('/dav');

        $sabreRequest = new SabreRequest(
            $request->getMethod(),
            $request->getRequestUri(),
            $request->headers->all(),
            $request->getContent(),
        );

        $sabreResponse = new SabreResponse();

        $server->invokeMethod($sabreRequest, $sabreResponse, false);

        $response = new Response(
            $sabreResponse->getBodyAsString(),
            $sabreResponse->getStatus(),
        );

        foreach ($sabreResponse->getHeaders() as $name => $values) {
            $response->headers->set($name, $values);
        }

        return $response;
    }
}
