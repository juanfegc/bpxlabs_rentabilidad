<?php

namespace App\Tests\Pricing;

use App\Entity\{CostType, Product, ProductCost};
use App\Pricing\{MarginCalculator, ProductProfitability, ProfitabilityReport};
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\{EntityManager, ORMSetup};
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class ProfitabilityReportTest extends TestCase
{
    private EntityManager $entityManager;
    private ProfitabilityReport $report;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration([dirname(__DIR__, 2) . '/src/Entity'], true);
        $this->entityManager = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]), $config);
        (new SchemaTool($this->entityManager))->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        $this->report = new ProfitabilityReport($this->entityManager, new ProductProfitability(new MarginCalculator()));
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
    }

    public function testEmptyReportHasSafeChartScale(): void
    {
        $report = $this->report->build();
        self::assertSame([], $report['products']);
        self::assertSame(1.0, $report['chartMax']);
        self::assertSame(0, array_sum($report['counts']));
    }

    public function testAggregatesCostsAndReflectsEditsAndDeletions(): void
    {
        $type = (new CostType())->setName('Fabricación');
        $priced = (new Product())->setName('A completo')->setRetailPrice('110');
        $noPrice = (new Product())->setName('B sin precio');
        $noCosts = (new Product())->setName('C sin costes')->setSalePrice('80');
        $firstCost = (new ProductCost())->setProduct($priced)->setCostType($type)->setAmount('10.1234');
        $secondCost = (new ProductCost())->setProduct($priced)->setCostType($type)->setAmount('49.8766');
        $pendingCost = (new ProductCost())->setProduct($noPrice)->setCostType($type)->setAmount('20');
        foreach ([$type, $priced, $noPrice, $noCosts, $firstCost, $secondCost, $pendingCost] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        $report = $this->report->build();
        self::assertCount(3, $report['products']);
        self::assertSame(['profit' => 1, 'loss' => 0, 'break_even' => 0, 'pending' => 2], $report['counts']);
        self::assertSame(100.0, $report['chartMax']);
        self::assertSame(60.0, $report['products'][0]['totalCost']);
        self::assertSame(2, $report['products'][0]['costCount']);
        self::assertSame(40.0, $report['products'][0]['profit']);
        self::assertSame(40.0, $report['products'][0]['margin']);
        self::assertNull($report['products'][1]['salePrice']);
        self::assertSame(0, $report['products'][2]['costCount']);

        $priced->setSalePrice('50');
        $this->entityManager->flush();
        $report = $this->report->build();
        self::assertSame(-10.0, $report['products'][0]['profit']);
        self::assertSame(1, $report['counts']['loss']);

        $this->entityManager->remove($secondCost);
        $this->entityManager->flush();
        $report = $this->report->build();
        self::assertSame(39.8766, $report['products'][0]['profit']);
        self::assertSame(1, $report['products'][0]['costCount']);

        $this->entityManager->remove($firstCost);
        $this->entityManager->flush();
        self::assertSame('pending', $this->report->build()['products'][0]['status']);
    }
}
