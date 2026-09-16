<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class ProductCost
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: CostType::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?CostType $costType = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 4)]
    #[Assert\NotBlank, Assert\PositiveOrZero]
    #[Assert\Regex(pattern: '/^\d{1,8}(\.\d{1,4})?$/', message: 'Usa hasta 8 enteros y 4 decimales, separados por punto.')]
    private string $amount = '0.0000';

    public function getId(): ?int { return $this->id; }
    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): static { $this->product = $product; return $this; }
    public function getCostType(): ?CostType { return $this->costType; }
    public function setCostType(?CostType $costType): static { $this->costType = $costType; return $this; }
    public function getAmount(): string { return $this->amount; }
    public function setAmount(string $amount): static { $this->amount = $amount; return $this; }
}
