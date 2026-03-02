<?php

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Ensure the test database schema is in sync with the current entity mapping.
$kernel = new App\Kernel('test', true);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
(new SchemaTool($em))->updateSchema($em->getMetadataFactory()->getAllMetadata(), true);
$kernel->shutdown();
