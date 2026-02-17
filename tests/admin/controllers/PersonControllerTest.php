<?php

namespace App\Tests\admin\controllers;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\PersonCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Test\Trait\CrudTestFormAsserts;

/**
 * @extends AbstractCrudTestCase<PersonCrudController>
 */
final class PersonControllerTest extends AbstractCrudTestCase
{
    use CrudTestFormAsserts;

    protected function getControllerFqcn(): string
    {
        return PersonCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    public function testAccess(): void
    {
        $this->client->followRedirects();

        $this->client->request("GET", $this->generateIndexUrl());

        static::assertResponseIsSuccessful();
    }
}
