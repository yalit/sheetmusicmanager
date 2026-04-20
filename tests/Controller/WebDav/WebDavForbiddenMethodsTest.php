<?php

namespace App\Tests\Controller\WebDav;

use App\DataFixtures\WebDavTokenFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WebDavForbiddenMethodsTest extends WebTestCase
{
    private const DAV_URL = '/dav/sheets/';

    // -------------------------------------------------------------------------
    // Data providers
    // -------------------------------------------------------------------------

    /**
     * @return iterable<string, array{string}>
     */
    public static function forbiddenMethods(): iterable
    {
        yield 'PUT'       => ['PUT'];
        yield 'DELETE'    => ['DELETE'];
        yield 'MKCOL'     => ['MKCOL'];
        yield 'COPY'      => ['COPY'];
        yield 'MOVE'      => ['MOVE'];
        yield 'PATCH'     => ['PATCH'];
        yield 'POST'      => ['POST'];
        yield 'LOCK'      => ['LOCK'];
        yield 'UNLOCK'    => ['UNLOCK'];
        yield 'PROPPATCH' => ['PROPPATCH'];
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider forbiddenMethods
     */
    public function testForbiddenMethodReturns405(string $method): void
    {
        $client = static::createClient();
        $client->request($method, self::DAV_URL, [], [], [
            'PHP_AUTH_USER' => 'admin@sheetmusic.test',
            'PHP_AUTH_PW'   => WebDavTokenFixtures::ADMIN_PLAIN_TOKEN,
        ]);

        static::assertResponseStatusCodeSame(405);
    }
}
