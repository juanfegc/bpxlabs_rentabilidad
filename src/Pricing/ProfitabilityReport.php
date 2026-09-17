<?php

namespace App\Pricing;

use App\Entity\{Product, ProductCost};
use Doctrine\ORM\EntityManagerInterface;

final class ProfitabilityReport
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductProfitability $profitability,
    ) {}

    public function build(bool $discountEnabled = false): array
    {
        $products = $this->entityManager->createQueryBuilder()
            ->select('p.id, p.name, p.color, p.salePrice, COALESCE(SUM(c.amount), 0) AS totalCost, COUNT(c.id) AS costCount')
            ->from(Product::class, 'p')
            ->leftJoin(ProductCost::class, 'c', 'WITH', 'c.product = p')
            ->groupBy('p.id, p.name, p.color, p.salePrice')
            ->orderBy('p.name', 'ASC')
            ->getQuery()->getArrayResult();

        $counts = ['profit' => 0, 'loss' => 0, 'break_even' => 0, 'pending' => 0];
        $normalCounts = $counts;
        $chartMax = 0.0;
        foreach ($products as &$product) {
            $product['totalCost'] = (float) $product['totalCost'];
            $product['salePrice'] = $product['salePrice'] === null ? null : (float) $product['salePrice'];
            $product['costCount'] = (int) $product['costCount'];
            $product['normalPrice'] = $product['salePrice'];
            $product['normal'] = $this->profitability->calculate($product['totalCost'], $product['normalPrice'], $product['costCount']);
            ++$normalCounts[$product['normal']['status']];
            if ($discountEnabled && $product['salePrice'] !== null) {
                $product['salePrice'] = round($product['salePrice'] * 0.9, 4);
            }
            $product += $this->profitability->calculate($product['totalCost'], $product['salePrice'], $product['costCount']);
            $product['profitDifference'] = $product['profit'] === null ? null : round($product['profit'] - $product['normal']['profit'], 4);
            $product['marginDifference'] = $product['margin'] === null || $product['normal']['margin'] === null
                ? null : $product['margin'] - $product['normal']['margin'];
            ++$counts[$product['status']];
            $chartMax = max($chartMax, $product['totalCost'], $product['normalPrice'] ?? 0);
        }
        unset($product);

        return ['products' => $products, 'counts' => $counts, 'normalCounts' => $normalCounts,
            'discountEnabled' => $discountEnabled, 'chartMax' => $chartMax > 0 ? $chartMax : 1.0];
    }
}
