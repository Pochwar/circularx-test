<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity("email")]
#[ORM\Table(name: '`user`')]
#[ApiResource(normalizationContext: ['groups' => ['user']])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(["user", "takeover"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Email]
    #[Groups(["user", "takeover"])]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Takeover::class, orphanRemoval: true)]
    #[Groups("user")]
    private Collection $takeovers;

    public function __construct()
    {
        $this->takeovers = new ArrayCollection();
    }

    public static function create(string $email): self
    {
        $user = new self();
        $user->setEmail($email);

        return $user;
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
