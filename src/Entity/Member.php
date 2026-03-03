<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use App\Enum\MemberRole;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Exception;
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
    private ?string $password = null;

    #[ORM\Column(length: 50, enumType: MemberRole::class)]
    private MemberRole $role = MemberRole::Member;

    public function eraseCredentials(): void {
        $this->plainPassword = null;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string {
        if(null === $this->email || $this->email === '') {
            throw new Exception("Member email shouldn't be null or empty");
        }
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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): static
    {
        $this->plainPassword = $password;

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

    public function getRole(): MemberRole
    {
        return $this->role;
    }

    public function setRole(?MemberRole $role): static
    {
        $this->role = $role ?? MemberRole::Member;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role->value)];
    }

    public function __toString(): string
    {
        return $this->name ?? $this->email ?? '';
    }
}
