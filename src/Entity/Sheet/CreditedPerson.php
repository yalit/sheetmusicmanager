<?php

namespace App\Entity\Sheet;

use App\Repository\CreditedPersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CreditedPersonRepository::class)]
class CreditedPerson
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PersonType $personType = null;

    #[ORM\ManyToOne(inversedBy: 'credit')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sheet $sheet = null;

    #[ORM\ManyToOne(inversedBy: 'credit')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonType(): ?PersonType
    {
        return $this->personType;
    }

    public function setPersonType(?PersonType $personType): static
    {
        $this->personType = $personType;

        return $this;
    }

    public function getSheet(): ?Sheet
    {
        return $this->sheet;
    }

    public function setSheet(?Sheet $sheet): static
    {
        $this->sheet = $sheet;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function __toString(): string
    {
        return "{$this->person?->getName()} ({$this->personType?->getName()})";
    }
}
