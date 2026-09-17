<?php
namespace App\Controller\Admin;
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\{ColorField, IdField, TextField};

final class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Product::class; }
    public function configureCrud(Crud $crud): Crud { return $crud->setEntityLabelInPlural('Productos'); }
    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addJsFile('js/product-price.js');
    }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nombre');
        yield ColorField::new('color', 'Color')
            ->setHelp('Se propone un color aleatorio. Pulsa el cuadro para elegir el color del producto en el gráfico.');
        yield TextField::new('retailPrice', 'PVP con IVA (€)')
            ->setRequired(false)
            ->setFormTypeOption('attr', ['data-retail-price' => '', 'data-vat-percent' => Product::VAT_PERCENT, 'inputmode' => 'decimal'])
            ->setHelp('IVA incluido del ' . Product::VAT_PERCENT . ' %. Separador decimal: punto; hasta 4 decimales. Déjalo vacío si aún no lo conoces.')
            ->formatValue(static fn ($value) => $value === null ? 'Sin precio' : number_format((float) $value, 4, ',', '.') . ' €');
        yield TextField::new('salePrice', 'PVU sin IVA (€)')
            ->setRequired(false)
            ->setFormTypeOption('disabled', true)
            ->setFormTypeOption('attr', ['data-net-price' => ''])
            ->setHelp('Se calcula automáticamente a partir del PVP. Es el precio utilizado para calcular la rentabilidad.')
            ->formatValue(static fn ($value) => $value === null ? 'Sin precio' : number_format((float) $value, 4, ',', '.') . ' €');
    }
}
