<?php

namespace App\Entity\WebDAV\Sheets;

use App\Entity\Sheet\Sheet;
use App\Service\DataProviders\DataProvider;
use App\Service\WebDAV\NameGenerator;
use Sabre\DAV\File;

class DAVSheetFile extends File
{
    public function __construct(
        private readonly Sheet                $sheet,
        private readonly DataProvider         $pdfProvider,
        private readonly NameGenerator         $nameGenerator,
    ) {}

    public function getName(): string
    {
        return $this->nameGenerator->generate($this->sheet);
    }

    public function get(): string
    {
        return $this->pdfProvider->getContent($this->sheet);
    }

    public function getSize(): ?int
    {
        return $this->pdfProvider->getSize($this->sheet);
    }

    public function getContentType(): string
    {
        return 'application/pdf';
    }

    public function getLastModified(): int
    {
        return ($this->sheet->getUpdatedAt() ?? new \DateTimeImmutable())->getTimestamp();
    }

    public function getETag(): string
    {
        return '"' . md5((string) $this->getLastModified()) . '"';
    }
}
