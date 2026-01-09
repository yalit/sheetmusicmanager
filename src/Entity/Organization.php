<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/** @package App\Entity */
#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $persons;

    /**
     * @var Collection<int, Sheet>
     */
    #[ORM\OneToMany(targetEntity: Sheet::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $sheets;

    /**
     * @var Collection<int, CreditedPerson>
     */
    #[ORM\OneToMany(targetEntity: CreditedPerson::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $creditedPeople;

    /**
     * @var Collection<int, Setlist>
     */
    #[ORM\OneToMany(targetEntity: Setlist::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $setlists;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\ManyToMany(targetEntity: Member::class, mappedBy: 'organizations')]
    private Collection $members;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->sheets = new ArrayCollection();
        $this->creditedPeople = new ArrayCollection();
        $this->setlists = new ArrayCollection();
        $this->members = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->persons->contains($person)) {
            $this->persons->add($person);
            $person->setOrganization($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->persons->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getOrganization() === $this) {
                $person->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sheet>
     */
    public function getSheets(): Collection
    {
        return $this->sheets;
    }

    public function addSheet(Sheet $sheet): static
    {
        if (!$this->sheets->contains($sheet)) {
            $this->sheets->add($sheet);
            $sheet->setOrganization($this);
        }

        return $this;
    }

    public function removeSheet(Sheet $sheet): static
    {
        if ($this->sheets->removeElement($sheet)) {
            // set the owning side to null (unless already changed)
            if ($sheet->getOrganization() === $this) {
                $sheet->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CreditedPerson>
     */
    public function getCreditedPeople(): Collection
    {
        return $this->creditedPeople;
    }

    public function addCreditedPerson(CreditedPerson $creditedPerson): static
    {
        if (!$this->creditedPeople->contains($creditedPerson)) {
            $this->creditedPeople->add($creditedPerson);
            $creditedPerson->setOrganization($this);
        }

        return $this;
    }

    public function removeCreditedPerson(CreditedPerson $creditedPerson): static
    {
        if ($this->creditedPeople->removeElement($creditedPerson)) {
            // set the owning side to null (unless already changed)
            if ($creditedPerson->getOrganization() === $this) {
                $creditedPerson->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Setlist>
     */
    public function getSetlists(): Collection
    {
        return $this->setlists;
    }

    public function addSetlist(Setlist $setlist): static
    {
        if (!$this->setlists->contains($setlist)) {
            $this->setlists->add($setlist);
            $setlist->setOrganization($this);
        }

        return $this;
    }

    public function removeSetlist(Setlist $setlist): static
    {
        if ($this->setlists->removeElement($setlist)) {
            // set the owning side to null (unless already changed)
            if ($setlist->getOrganization() === $this) {
                $setlist->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->addOrganization($this);
        }

        return $this;
    }

    public function removeMember(Member $member): static
    {
        if ($this->members->removeElement($member)) {
            $member->removeOrganization($this);
        }

        return $this;
    }
}
