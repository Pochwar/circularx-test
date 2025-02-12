<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity(fields: ['name', 'brand'])]
#[ApiResource(normalizationContext: ['groups' => ['product']])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["product", "takeover"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(["product", "takeover"])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["product", "takeover"])]
    private ?Brand $brand = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Groups(["product", "takeover"])]
    #[SerializedName('original_price')]
    private ?int $price = null;

    public static function create(Brand $brand, string $name, int $price): self
    {
        $product = new self();
        $product->setName($name);
        $product->setBrand($brand);
        $product->setPrice($price);

        return $product;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
