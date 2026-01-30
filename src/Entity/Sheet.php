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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

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
    #[NotBlank]
    #[NotNull]
    #[Length(min: 3, max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Length(min: 3, max: 100)]
    private ?string $genre = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Length(min: 3, max: 20)]
    private ?string $difficulty = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Length(max: 50)]
    private ?string $duration = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Length(max: 50)]
    private ?string $key_signature = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /**
     * @var array<int, array<string, string>>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $refs = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Length(max: 255)]
    private ?string $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Length(max: 255)]
    private ?string $fullPath = null;

    /**
     * @var Collection<int, CreditedPerson>
     */
    #[ORM\OneToMany(targetEntity: CreditedPerson::class, mappedBy: 'sheet', orphanRemoval: true)]
    private Collection $credit;

    /**
     * @var Collection<int, SetListItem>
     */
    #[ORM\OneToMany(targetEntity: SetListItem::class, mappedBy: 'sheet', orphanRemoval: true)]
    private Collection $setlist;

    public function __construct()
    {
        $this->credit = new ArrayCollection();
        $this->setlist = new ArrayCollection();
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
     * @return list<SheetReference>
     **/
    public function getRefs(): array
    {
        return array_values(array_map(
            fn(array $ref): SheetReference => SheetReference::fromArray($ref),
            $this->refs
        ));
    }

    /**
     * @param list<SheetReference> $refs
     **/
    public function setRefs(array $refs): static
    {
        $this->refs = array_map(
            fn(SheetReference $ref): array => $ref->toArray(),
            $refs
        );

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

    /**
     * @return Collection<int, SetListItem>
     */
    public function getSetlist(): Collection
    {
        return $this->setlist;
    }

    public function addSetlist(SetListItem $setlist): static
    {
        if (!$this->setlist->contains($setlist)) {
            $this->setlist->add($setlist);
            $setlist->setSheet($this);
        }

        return $this;
    }

    public function removeSetlist(SetListItem $setlist): static
    {
        if ($this->setlist->removeElement($setlist)) {
            // set the owning side to null (unless already changed)
            if ($setlist->getSheet() === $this) {
                $setlist->setSheet(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getFullPath(): ?string
    {
        return $this->fullPath;
    }

    public function setFullPath(?string $fullPath): void
    {
        $this->fullPath = $fullPath;
    }
}
