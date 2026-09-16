<?php
namespace App\Controller\Admin;
use App\Entity\ProductCost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, AssociationField};

final class ProductCostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return ProductCost::class; }
    public function configureCrud(Crud $crud): Crud { return $crud->setEntityLabelInPlural('Costes de producto'); }
    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('product', 'Producto'));
    }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('product', 'Producto');
        yield AssociationField::new('costType', 'Tipo de coste');
        yield TextField::new('amount', 'Coste unitario (€)')->setHelp('Sin IVA. Separador decimal: punto; hasta 4 decimales.');
    }
}
