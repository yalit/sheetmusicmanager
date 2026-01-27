<?php

namespace App\Tests\admin\dashboard;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class DashboardControllerTest extends WebTestCase
{
    public function testDisplayAdmin(): void
    {

        $client = static::createClient();
        $client->request(Request::METHOD_GET, "/admin");

        static::assertResponseRedirects();
        $client->followRedirect();
        static::assertResponseIsSuccessful();
    }
}
