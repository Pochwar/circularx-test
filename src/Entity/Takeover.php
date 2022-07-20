<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\TakeoverRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TakeoverRepository::class)]
#[ApiResource(normalizationContext: ['groups' => ['takeover']])]
#[ApiFilter(SearchFilter::class, properties: ['products.product.id' => 'exact', 'products.product.brand.id' => 'exact'])]
class Takeover
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups("takeover")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'takeovers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups("takeover")]
    private ?User $owner = null;

    #[ORM\OneToMany(mappedBy: 'takeover', targetEntity: ProductTakeover::class, cascade: ['persist', 'remove'])]
    #[Groups("takeover")]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public static function create(User $user, array $products = []): self
    {
        $takeover = new self();
        $takeover->setOwner($user);

        foreach ($products as $product) {
            $productTakeover = ProductTakeover::create($product, $takeover);
            $takeover->addProduct($productTakeover);
        }

        return $takeover;
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

    #[Groups("takeover")]
    public function getTotalPrice(): int
    {
        $totalPrice = 0;
        foreach ($this->getProducts() as $product) {
            $totalPrice += $product->getPrice();
        }

        return $totalPrice;
    }
}
