<?php

namespace App\Service\ForScore;

use App\Entity\Setlist\Setlist;
use App\Entity\Setlist\SetListItem;
use App\Service\SheetPdfProvider;
use App\Service\WebDAV\NameGenerator;
use App\Service\WebDAV\SheetFilenameGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ForScore4SSExporter
{
    public function __construct(
        #[Autowire(service: SheetFilenameGenerator::class)]
        private NameGenerator    $filenameGenerator,
        private SheetPdfProvider $pdfProvider,
    ) {}

    public function export(Setlist $setlist): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('forScore');
        $root->setAttribute('kind', 'setlist');
        $root->setAttribute('version', '1.0');
        $root->setAttribute('title', $setlist->getTitle());
        $dom->appendChild($root);

        foreach ($setlist->getItems() as $item) {
            $root->appendChild($this->buildEntry($dom, $item));
        }

        return $dom->saveXML();
    }

    private function buildEntry(\DOMDocument $dom, SetListItem $item): \DOMElement
    {
        $filename = $this->filenameGenerator->generate($item->getSheet());

        if ($this->pdfProvider->hasContent($item->getSheet())) {
            $node = $dom->createElement('score');
            $node->setAttribute('path', $filename);
        } else {
            $node = $dom->createElement('placeholder');
            $node->setAttribute('title', $filename);
        }

        return $node;
    }
}
