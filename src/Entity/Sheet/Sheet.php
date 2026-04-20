<?php

namespace App\Entity\Sheet;

use App\Doctrine\ValueObjectArray;
use App\Entity\Setlist\SetListItem;
use App\Entity\Sheet\ValueObject\StoredFile;
use App\Repository\SheetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
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

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $tags = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $refs = [];

    /**
     * @var StoredFile[]
     */
    #[ORM\Column(type: ValueObjectArray::VALUE_OBJECT_ARRAY, options: ['class' => StoredFile::class])]
    private array $files = [];

    /**
     * @var array<UploadedFile>
     */
    #[Assert\All([
        new Assert\File(
            maxSize: '10M',
            mimeTypes: ['application/pdf'],
            mimeTypesMessage: 'Only PDF files are accepted.',
        )
    ])]
    private array $uploadedFiles = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Length(max: 255)]
    private ?string $fullPath = null;

    /**
     * @var Collection<int, CreditedPerson>
     */
    #[ORM\OneToMany(targetEntity: CreditedPerson::class, mappedBy: 'sheet', cascade: ['persist'], orphanRemoval: true)]
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

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;

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
     * @return string[]
     */
    public function getRefs(): array
    {
        return $this->refs;
    }

    /**
     * @param string[] $refs
     */
    public function setRefs(array $refs): static
    {
        $this->refs = $refs;

        return $this;
    }

    /**
     * @return StoredFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param StoredFile[]|null $files
     * @return $this
     */
    public function setFiles(?array $files): static
    {
        $this->files = $files ?? [];

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
        if (!$this->title) {
            return '';
        }

        return $this->title . (count($this->refs) > 0 ? " (" . implode(', ', $this->refs) . ")" : "");
    }

    /**
     * @return array<UploadedFile>
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @param array<UploadedFile> $files
     */
    public function setUploadedFiles(array $files): static
    {
        $this->uploadedFiles = $files;
        return $this;
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
