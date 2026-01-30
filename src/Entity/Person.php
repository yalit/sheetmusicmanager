<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    /**
     * @var Collection<int, CreditedPerson>
     */
    #[ORM\OneToMany(targetEntity: CreditedPerson::class, mappedBy: 'person', orphanRemoval: true)]
    private Collection $credit;

    public function __construct()
    {
        $this->credit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, CreditedPerson>
     */
    public function getCredit(): Collection
    {
        return $this->credit;
    }

    public function addCredit(CreditedPerson $credit): static
    {
        if (!$this->credit->contains($credit)) {
            $this->credit->add($credit);
            $credit->setPerson($this);
        }

        return $this;
    }

    public function removeCredit(CreditedPerson $credit): static
    {
        if ($this->credit->removeElement($credit)) {
            // set the owning side to null (unless already changed)
            if ($credit->getPerson() === $this) {
                $credit->setPerson(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
