<?php
namespace App\Tests\Pricing;
use App\Pricing\MarginCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
final class MarginCalculatorTest extends TestCase
{
    #[DataProvider('validCases')]
    public function testCalculations(string $method, float $cost, float $value, float $expected): void
    {
        self::assertEqualsWithDelta($expected, (new MarginCalculator())->$method($cost, $value), 0.000001);
    }
    public static function validCases(): iterable
    {
        yield ['marginPercent', 60, 100, 40];
        yield ['markupPercent', 60, 100, 66.6666666667];
        yield ['targetPriceForMargin', 60, 40, 100];
        yield ['marginPercent', 100, 80, -25];
        yield ['markupPercent', 100, 80, -20];
        yield ['marginPercent', 0, 80, 100];
        yield ['markupPercent', 80, 0, -100];
        yield ['marginPercent', 80, 80, 0];
        yield ['targetPriceForMargin', 80, 0, 80];
        yield ['targetPriceForMargin', 1, 99, 100];
    }
    #[DataProvider('invalidCases')]
    public function testInvalidInputs(string $method, float $cost, float $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new MarginCalculator())->$method($cost, $value);
    }
    public static function invalidCases(): iterable
    {
        yield ['marginPercent', 10, 0];
        yield ['markupPercent', 0, 10];
        yield ['targetPriceForMargin', 0, 40];
        yield ['targetPriceForMargin', 10, 100];
        yield ['targetPriceForMargin', 10, 101];
        foreach (['marginPercent', 'markupPercent', 'targetPriceForMargin'] as $method) {
            foreach ([-1, INF, NAN] as $invalid) {
                yield [$method, $invalid, 10];
                yield [$method, 10, $invalid];
            }
        }
    }
}
