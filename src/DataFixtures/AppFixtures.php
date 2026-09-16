<?php
namespace App\DataFixtures;
use App\Entity\CostType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (['Materia prima', 'Envase', 'Etiqueta', 'Sleeve', 'Dosificador', 'Fabricación', 'Transporte', 'Comisión', 'Otros'] as $name) {
            if (!$manager->getRepository(CostType::class)->findOneBy(['name' => $name])) {
                $manager->persist((new CostType())->setName($name));
            }
        }
        $manager->flush();
    }
}
