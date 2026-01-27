<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Organization;
use App\Entity\Sheet;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

/** @package App\Controller\Admin */
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirectToRoute("admin_organization_index");
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sheetmusic Manager');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Organizations', 'fa fa-building', Organization::class);
        yield MenuItem::linkToCrud('Members', 'fa fa-users', Member::class);

        yield MenuItem::section('Partitions');
        yield MenuItem::linkToCrud('Sheets', 'fa fa-music', Sheet::class);
    }
}
