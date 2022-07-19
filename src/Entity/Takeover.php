<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TakeoverRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TakeoverRepository::class)]
#[ApiResource]
class Takeover
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'takeovers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\OneToMany(mappedBy: 'takeover', targetEntity: ProductTakeover::class, cascade: ['persist', 'remove'])]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, ProductTakeover>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(ProductTakeover $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setTakeover($this);
        }

        return $this;
    }

    public function removeProduct(ProductTakeover $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getTakeover() === $this) {
                $product->setTakeover(null);
            }
        }

        return $this;
    }
}
