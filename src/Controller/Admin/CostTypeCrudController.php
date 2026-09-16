<?php
namespace App\Controller\Admin;
use App\Entity\CostType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField, AssociationField};

final class CostTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return CostType::class; }
    public function configureCrud(Crud $crud): Crud { return $crud->setEntityLabelInPlural('Tipos de coste'); }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nombre');
    }
}
