<?php

namespace App\Tests\Pricing;

use App\Entity\Product;
use App\Pricing\{MarginCalculator, ProductProfitability};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ProductProfitabilityTest extends TestCase
{
    #[DataProvider('scenarios')]
    public function testProfitability(float $cost, ?float $price, int $costCount, ?float $profit, ?float $margin, string $status): void
    {
        $result = (new ProductProfitability(new MarginCalculator()))->calculate($cost, $price, $costCount);
        self::assertSame($profit, $result['profit']);
        self::assertSame($status, $result['status']);
        if ($margin === null) {
            self::assertNull($result['margin']);
        } else {
            self::assertEqualsWithDelta($margin, $result['margin'], 0.000001);
        }
    }

    public static function scenarios(): iterable
    {
        yield 'beneficio' => [60, 100, 2, 40.0, 40.0, 'profit'];
        yield 'pérdida' => [100, 80, 1, -20.0, -25.0, 'loss'];
        yield 'equilibrio' => [80, 80, 1, 0.0, 0.0, 'break_even'];
        yield 'precio pendiente' => [60, null, 1, null, null, 'pending'];
        yield 'costes pendientes' => [0, 100, 0, null, null, 'pending'];
        yield 'ambos pendientes' => [0, null, 0, null, null, 'pending'];
        yield 'coste cero explícito' => [0, 100, 1, 100.0, 100.0, 'profit'];
        yield 'precio cero' => [60, 0, 1, -60.0, null, 'loss'];
        yield 'ambos cero' => [0, 0, 1, 0.0, null, 'break_even'];
        yield 'cuatro decimales' => [0.0001, 0.0003, 1, 0.0002, 66.6666666667, 'profit'];
        yield 'sin residuos binarios' => [0.1 + 0.2, 0.3, 2, 0.0, 0.0, 'break_even'];
    }

    #[DataProvider('prices')]
    public function testPriceValidation(?string $price, bool $valid): void
    {
        $product = (new Product())->setSalePrice($price);
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        self::assertSame($valid, count($validator->validateProperty($product, 'salePrice')) === 0);
    }

    public static function prices(): iterable
    {
        yield [null, true];
        yield ['', true];
        yield ['0', true];
        yield ['12.3456', true];
        yield ['99999999.9999', true];
        yield ['-1', false];
        yield ['100000000', false];
        yield ['1.12345', false];
        yield ['1,5', false];
        yield ['abc', false];
    }
}
