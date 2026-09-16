<?php
namespace App\Pricing;

/** Importes unitarios sin impuestos y en la misma moneda. Porcentajes en escala 0–100. */
final class MarginCalculator
{
    public function marginPercent(float $cost, float $price): float
    {
        $this->validate($cost, $price);
        if ($price <= 0) { throw new \InvalidArgumentException('El precio debe ser mayor que cero.'); }
        return ($price - $cost) / $price * 100;
    }

    public function markupPercent(float $cost, float $price): float
    {
        $this->validate($cost, $price);
        if ($cost <= 0) { throw new \InvalidArgumentException('El coste debe ser mayor que cero.'); }
        return ($price - $cost) / $cost * 100;
    }

    public function targetPriceForMargin(float $cost, float $marginPercent): float
    {
        $this->validate($cost, $marginPercent);
        if ($cost <= 0 || $marginPercent >= 100) {
            throw new \InvalidArgumentException('Se requiere coste positivo y margen entre 0 (incluido) y 100 (excluido).');
        }
        return $cost / (1 - $marginPercent / 100);
    }

    private function validate(float ...$values): void
    {
        foreach ($values as $value) {
            if (!is_finite($value) || $value < 0) {
                throw new \InvalidArgumentException('Los valores deben ser finitos y no negativos.');
            }
        }
    }
}
