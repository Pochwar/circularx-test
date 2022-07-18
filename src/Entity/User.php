<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Takeover::class, orphanRemoval: true)]
    private Collection $takeovers;

    public function __construct()
    {
        $this->takeovers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Takeover>
     */
    public function getTakeovers(): Collection
    {
        return $this->takeovers;
    }

    public function addTakeover(Takeover $takeover): self
    {
        if (!$this->takeovers->contains($takeover)) {
            $this->takeovers[] = $takeover;
            $takeover->setOwner($this);
        }

        return $this;
    }

    public function removeTakeover(Takeover $takeover): self
    {
        if ($this->takeovers->removeElement($takeover)) {
            // set the owning side to null (unless already changed)
            if ($takeover->getOwner() === $this) {
                $takeover->setOwner(null);
            }
        }

        return $this;
    }
}
