<?php

namespace App\Entity;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Repository\ProductTakeoverRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductTakeoverRepository::class)]
class ProductTakeover
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("takeover")]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Takeover $takeover = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Groups("takeover")]
    private ?int $price = null;

    public static function create(Product $product, Takeover $takeover): self
    {
        $productTakeover = new self();
        $productTakeover->setProduct($product);
        $productTakeover->setTakeover($takeover);
        $productTakeover->setPrice($product->getPrice());

        return $productTakeover;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getTakeover(): ?Takeover
    {
        return $this->takeover;
    }

    public function setTakeover(?Takeover $takeover): self
    {
        $this->takeover = $takeover;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        /**
         * #[Assert\Positive] on $price property is not working
         * Maybe because it's a pivot entity?
         * So let's throw a ValidationException instead for now.
         */
        if ($price < 0) {
            throw new ValidationException('price: This value must be positive.');
        }
        $this->price = $price;

        return $this;
    }
}
