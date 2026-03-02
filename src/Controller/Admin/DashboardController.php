<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\Setlist;
use App\Entity\Sheet;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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
        return $this->redirectToRoute("admin_sheet_index");
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sheetmusic Manager')
        ;
    }

    public function configureCrud(): Crud
    {
        $crud = parent::configureCrud();

        return $crud
            ->setFormThemes(['admin/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->renderContentMaximized()
            ->setPaginatorPageSize(25)
        ;
    }

    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets();

        return $assets->addAssetMapperEntry('app');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Members', 'fa fa-users', Member::class);

        yield MenuItem::section('Partitions');
        yield MenuItem::linkToCrud('Person Types', 'fa fa-tags', PersonType::class);
        yield MenuItem::linkToCrud('Persons', 'fa fa-user', Person::class);
        yield MenuItem::linkToCrud('Sheets', 'fa fa-music', Sheet::class);

        yield MenuItem::section('Performances');
        yield MenuItem::linkToCrud('Setlists', 'fa fa-list', Setlist::class);
    }
}
