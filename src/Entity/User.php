<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse email.')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le pseudo est obligatoire.')]
    #[Assert\Length(min: 3, max: 50)]
    private ?string $pseudo = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'âge est obligatoire.')]
    #[Assert\Range(min: 13, max: 120, notInRangeMessage: 'L\'âge doit être entre 13 et 120 ans.')]
    private ?int $age = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private int $failedLoginAttempts = 0;

    #[ORM\Column]
    private bool $isBlocked = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Photo::class, orphanRemoval: true)]
    private Collection $photos;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function setFailedLoginAttempts(int $failedLoginAttempts): static
    {
        $this->failedLoginAttempts = $failedLoginAttempts;
        return $this;
    }

    public function incrementFailedLoginAttempts(): static
    {
        $this->failedLoginAttempts++;
        if ($this->failedLoginAttempts >= 3) {
            $this->isBlocked = true;
        }
        return $this;
    }

    public function resetFailedLoginAttempts(): static
    {
        $this->failedLoginAttempts = 0;
        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setUser($this);
        }
        return $this;
    }

    public function removePhoto(Photo $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getUser() === $this) {
                $photo->setUser(null);
            }
        }
        return $this;
    }

    public function getPublishedPhotos(): array
    {
        $published = [];
        foreach ($this->photos as $photo) {
            if ($photo->getDisplayOrder() !== null) {
                $published[] = $photo;
            }
        }
        usort($published, fn($a, $b) => $a->getDisplayOrder() <=> $b->getDisplayOrder());
        return $published;
    }
}
