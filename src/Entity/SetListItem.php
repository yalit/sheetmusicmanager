<?php

namespace App\Entity;

use App\Repository\SetListItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: SetListItemRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_position_per_setlist', columns: ['setlist_id', 'position'])]
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
    private ?SetList $setlist = null;

    #[ORM\ManyToOne(inversedBy: 'setlist')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sheet $sheet = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[NotNull]
    private ?int $position = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $notes = "";

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSetlist(): ?SetList
    {
        return $this->setlist;
    }

    public function setSetlist(?SetList $setlist): static
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

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }
}
