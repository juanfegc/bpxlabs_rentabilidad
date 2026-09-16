<?php

namespace App\Pricing;

final class ProductProfitability
{
    public function __construct(private readonly MarginCalculator $calculator) {}

    /** @return array{profit: ?float, margin: ?float, status: string} */
    public function calculate(float $cost, ?float $price, int $costCount): array
    {
        if (!is_finite($cost) || $cost < 0 || $costCount < 0 || ($price !== null && (!is_finite($price) || $price < 0))) {
            throw new \InvalidArgumentException('Los importes deben ser finitos y no negativos.');
        }

        if ($price === null || $costCount === 0) {
            return ['profit' => null, 'margin' => null, 'status' => 'pending'];
        }

        // Los importes persistidos tienen cuatro decimales; evita residuos de coma flotante.
        $profit = round($price - $cost, 4);

        return [
            'profit' => $profit,
            'margin' => $price > 0 ? $this->calculator->marginPercent($cost, $price) : null,
            'status' => $profit > 0 ? 'profit' : ($profit < 0 ? 'loss' : 'break_even'),
        ];
    }
}
