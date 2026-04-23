<?php

namespace App\Entity\WebDAV\Setlist;

use App\Entity\Setlist\SetListItem;
use App\Service\DataProviders\DataProvider;
use App\Service\WebDAV\NameGenerator;
use Sabre\DAV\File;

class DAVSetlistFile extends File
{
    public function __construct(
        private readonly SetListItem   $item,
        private readonly DataProvider  $pdfProvider,
        private readonly NameGenerator $filenameGenerator,
    ) {}

    public function getName(): string
    {
        return $this->filenameGenerator->generate($this->item->getSheet());
    }

    public function get(): string
    {
        return $this->pdfProvider->getContent($this->item->getSheet());
    }

    public function getSize(): ?int
    {
        return $this->pdfProvider->getSize($this->item->getSheet());
    }

    public function getContentType(): string
    {
        return 'application/pdf';
    }

    public function getLastModified(): int
    {
        return ($this->item->getSheet()->getUpdatedAt() ?? new \DateTimeImmutable())->getTimestamp();
    }

    public function getETag(): string
    {
        return '"' . md5((string)$this->getLastModified()) . '"';
    }
}
