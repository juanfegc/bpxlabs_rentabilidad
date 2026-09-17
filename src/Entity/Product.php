<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity('name')]
class Product
{
    public const VAT_PERCENT = 10;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160, unique: true)]
    #[Assert\NotBlank, Assert\Length(max: 160)]
    private string $name = '';

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/D', message: 'Selecciona un color válido.')]
    private string $color;

    public function __construct()
    {
        $this->color = sprintf('#%06x', random_int(0, 0xffffff));
    }

    #[ORM\Column(type: 'decimal', precision: 12, scale: 4, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d{1,8}(\.\d{1,4})?$/', message: 'Usa hasta 8 enteros y 4 decimales, separados por punto.')]
    private ?string $salePrice = null;

    #[ORM\Column(type: 'decimal', precision: 13, scale: 4, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d{1,9}(\.\d{1,4})?$/D', message: 'Usa hasta 9 enteros y 4 decimales, separados por punto.')]
    private ?string $retailPrice = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = trim($name); return $this; }
    public function getColor(): string { return $this->color; }
    public function setColor(string $color): static { $this->color = strtolower(trim($color)); return $this; }
    public function getSalePrice(): ?string { return $this->salePrice; }
    public function setSalePrice(?string $salePrice): static
    {
        $this->salePrice = $salePrice === null || trim($salePrice) === '' ? null : trim($salePrice);
        $this->retailPrice = $this->salePrice !== null && is_numeric($this->salePrice)
            ? number_format((float) $this->salePrice * (1 + self::VAT_PERCENT / 100), 4, '.', '')
            : null;
        return $this;
    }
    public function getRetailPrice(): ?string { return $this->retailPrice; }
    public function setRetailPrice(?string $retailPrice): static
    {
        $this->retailPrice = $retailPrice === null || trim($retailPrice) === '' ? null : trim($retailPrice);
        $this->salePrice = $this->retailPrice !== null && preg_match('/^\d{1,9}(\.\d{1,4})?$/D', $this->retailPrice)
            ? number_format((float) $this->retailPrice / (1 + self::VAT_PERCENT / 100), 4, '.', '')
            : null;
        return $this;
    }
    public function __toString(): string { return $this->name; }
}
