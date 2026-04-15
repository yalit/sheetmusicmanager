<?php

namespace App\Entity\Setlist;

use App\Entity\Sheet\Sheet;
use App\Repository\SetListItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: SetListItemRepository::class)]
class SetListItem
{
    use TimestampableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'item')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Setlist $setlist = null;

    #[ORM\ManyToOne(inversedBy: 'setlist')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sheet $sheet = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $position = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $notes = "";

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSetlist(): ?Setlist
    {
        return $this->setlist;
    }

    public function setSetlist(?Setlist $setlist): static
    {
        $this->setlist = $setlist;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? "Position {$this->position}";
    }
}
