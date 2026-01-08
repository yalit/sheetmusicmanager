<?php

namespace App\Entity;

use App\DTO\SheetReference;
use App\Repository\SheetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: SheetRepository::class)]
class Sheet
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $difficulty = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $key_signature = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::JSON)]
    private array $refs = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\ManyToOne(inversedBy: 'sheets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    /**
     * @var Collection<int, CreditedPerson>
     */
    #[ORM\OneToMany(targetEntity: CreditedPerson::class, mappedBy: 'sheet', orphanRemoval: true)]
    private Collection $credit;

    public function __construct()
    {
        $this->credit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(?string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getKeySignature(): ?string
    {
        return $this->key_signature;
    }

    public function setKeySignature(?string $key_signature): static
    {
        $this->key_signature = $key_signature;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return SheetReference[]
     **/
    public function getRefs(): array
    {
        return array_map(fn($ref) => SheetReference::fromArray($ref), $this->refs);
    }

    /**
     * @param $refs SheetReference[]
     **/
    public function setRefs(array $refs): static
    {
        $this->refs = array_map(fn($ref) => $ref->toArray(), $refs);

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

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
            $credit->setSheet($this);
        }

        return $this;
    }

    public function removeCredit(CreditedPerson $credit): static
    {
        if ($this->credit->removeElement($credit)) {
            // set the owning side to null (unless already changed)
            if ($credit->getSheet() === $this) {
                $credit->setSheet(null);
            }
        }

        return $this;
    }
}
