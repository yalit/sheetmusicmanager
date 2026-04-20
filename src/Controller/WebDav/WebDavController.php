<?php

namespace App\Controller\WebDav;

use App\Entity\WebDAV\DAVRootDirectory;
use Exception;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Server;
use Sabre\HTTP\Request as SabreRequest;
use Sabre\HTTP\Response as SabreResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebDavController extends AbstractController
{
    private const BASE_URI = '/dav';

    public function __construct(
        private readonly DAVRootDirectory $rootDirectory,
    ) {}

    #[Route('/dav/{path}', name: 'webdav', requirements: ['path' => '.*'], methods: ['GET', 'HEAD', 'OPTIONS', 'PROPFIND'])]
    public function handle(Request $request): Response
    {
        $server = new Server($this->rootDirectory);
        $server->setBaseUri(self::BASE_URI);

        $sabreRequest = $this->getSabreRequest($request);

        $sabreResponse = new SabreResponse();

        try {
            $server->invokeMethod($sabreRequest, $sabreResponse, false);
        } catch (NotFound $e) {
            return new Response($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return $this->getResponse($sabreResponse);
    }

    private function getSabreRequest(Request $request): SabreRequest
    {
        return new SabreRequest(
            $request->getMethod(),
            str_replace(self::BASE_URI, '', $request->getRequestUri()),
            $request->headers->all(),
            $request->getContent(),
        );
    }

    private function getResponse(SabreResponse $sabreResponse): Response
    {
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
