<?php
namespace App\Controller\Admin;
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{ColorField, IdField, TextField};

final class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Product::class; }
    public function configureCrud(Crud $crud): Crud { return $crud->setEntityLabelInPlural('Productos'); }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nombre');
        yield ColorField::new('color', 'Color')
            ->setHelp('Se propone un color aleatorio. Pulsa el cuadro para elegir el color del producto en el gráfico.');
        yield TextField::new('salePrice', 'Precio de venta unitario (€)')
            ->setRequired(false)
            ->setHelp('Sin IVA. Separador decimal: punto; hasta 4 decimales. Déjalo vacío si aún no lo conoces.')
            ->formatValue(static fn ($value) => $value === null ? 'Sin precio' : number_format((float) $value, 4, ',', '.') . ' €');
    }
}
