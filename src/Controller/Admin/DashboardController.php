<?php
namespace App\Controller\Admin;
use App\Entity\{Product, CostType, ProductCost};
use App\Pricing\ProfitabilityReport;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Dashboard, MenuItem};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly ProfitabilityReport $report, private readonly RequestStack $requests) {}

    public function index(): Response
    {
        return $this->render('admin/index.html.twig', $this->report->build($this->requests->getCurrentRequest()?->query->get('discount') === '1'));
    }
    public function configureDashboard(): Dashboard { return Dashboard::new()->setTitle('Costes y márgenes'); }
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-home');
        yield MenuItem::linkToCrud('Productos', 'fa fa-box', Product::class);
        yield MenuItem::linkToCrud('Tipos de coste', 'fa fa-tags', CostType::class);
        yield MenuItem::linkToCrud('Costes de producto', 'fa fa-euro-sign', ProductCost::class);
    }
}
