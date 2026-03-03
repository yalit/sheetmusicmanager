<?php

namespace App\Tests\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class DashboardControllerTest extends WebTestCase
{
    public function testAdminRedirectsToLoginWhenAnonymous(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        static::assertResponseRedirects('/login');
    }
}
