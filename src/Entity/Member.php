<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[UniqueConstraint(fields: ['email'])]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;

    public const DEFAULT_ROLE = "ROLE_MEMBER";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[NotNull]
    #[NotBlank]
    #[Email]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    #[NotNull]
    #[NotBlank]
    #[Length(min: 3)]
    private ?string $name = null;

    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[NotNull]
    #[NotBlank]
    private ?string $password = null;

    /**
     * @var $roles string[]
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\ManyToMany(targetEntity: Organization::class, inversedBy: 'members')]
    private Collection $organizations;

    public function __construct()
    {
        $this->organizations = new ArrayCollection();
    }

    public function eraseCredentials(): void {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string {
        return $this->email;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        if (array_find($this->roles, fn($role) => $role === static::DEFAULT_ROLE)) {
            return array_merge([static::DEFAULT_ROLE], $this->roles);
        }

        return $this->roles;
    }

    /**
     * @param $roles string[]
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Organization>
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): static
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): static
    {
        $this->organizations->removeElement($organization);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? $this->email ?? '';
    }
}
