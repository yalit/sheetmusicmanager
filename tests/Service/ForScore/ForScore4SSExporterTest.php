<?php

namespace App\Tests\Service\ForScore;

use App\Entity\Setlist\Setlist;
use App\Entity\Setlist\SetListItem;
use App\Entity\Sheet\Sheet;
use App\Service\ForScore\ForScore4SSExporter;
use App\Service\SheetPdfProvider;
use App\Service\WebDAV\NameGenerator;
use PHPUnit\Framework\TestCase;

final class ForScore4SSExporterTest extends TestCase
{
    private function makeExporter(array $hasContentMap = []): ForScore4SSExporter
    {
        $filenameGenerator = $this->createMock(NameGenerator::class);
        $filenameGenerator->method('generate')->willReturnCallback(
            fn(Sheet $sheet) => $sheet->getTitle() . '.pdf'
        );

        $pdfProvider = $this->createMock(SheetPdfProvider::class);
        $pdfProvider->method('hasContent')->willReturnCallback(
            fn(Sheet $sheet) => $hasContentMap[$sheet->getTitle()] ?? true
        );

        return new ForScore4SSExporter($filenameGenerator, $pdfProvider);
    }

    private function parseXml(string $xml): \SimpleXMLElement
    {
        $element = simplexml_load_string($xml);
        static::assertNotFalse($element, 'Response is not valid XML');
        return $element;
    }

    // -------------------------------------------------------------------------
    // Root element
    // -------------------------------------------------------------------------

    public function testRootElementHasCorrectAttributes(): void
    {
        $setlist = (new Setlist())->setTitle('Sunday Mass');

        $xml = $this->makeExporter()->export($setlist);
        $root = $this->parseXml($xml);

        static::assertSame('forScore', $root->getName());
        static::assertSame('setlist', (string) $root['kind']);
        static::assertSame('1.0', (string) $root['version']);
        static::assertSame('Sunday Mass', (string) $root['title']);
    }

    public function testEmptySetlistProducesNoChildren(): void
    {
        $setlist = (new Setlist())->setTitle('Empty');

        $root = $this->parseXml($this->makeExporter()->export($setlist));

        static::assertCount(0, $root->children());
    }

    // -------------------------------------------------------------------------
    // Score entries
    // -------------------------------------------------------------------------

    public function testItemWithFileProducesScoreElement(): void
    {
        $sheet   = (new Sheet())->setTitle('Toccata');
        $item    = (new SetListItem())->setPosition(1)->setName('Opener')->setSheet($sheet);
        $setlist = (new Setlist())->setTitle('Concert');
        $setlist->addItem($item);

        $root = $this->parseXml($this->makeExporter()->export($setlist));

        static::assertCount(1, $root->score);
        static::assertSame('Toccata.pdf', (string) $root->score[0]['path']);
    }

    // -------------------------------------------------------------------------
    // Placeholder entries
    // -------------------------------------------------------------------------

    public function testItemWithoutFileProducesPlaceholderElement(): void
    {
        $sheet   = (new Sheet())->setTitle('Missing');
        $item    = (new SetListItem())->setPosition(1)->setName('Ghost')->setSheet($sheet);
        $setlist = (new Setlist())->setTitle('Concert');
        $setlist->addItem($item);

        $root = $this->parseXml($this->makeExporter(['Missing' => false])->export($setlist));

        static::assertCount(1, $root->placeholder);
        static::assertSame('Missing.pdf', (string) $root->placeholder[0]['title']);
        static::assertCount(0, $root->score);
    }

    // -------------------------------------------------------------------------
    // Mixed setlist
    // -------------------------------------------------------------------------

    public function testMixedSetlistProducesBothElements(): void
    {
        $sheetA = (new Sheet())->setTitle('Present');
        $sheetB = (new Sheet())->setTitle('Absent');

        $itemA = (new SetListItem())->setPosition(1)->setName('Opener')->setSheet($sheetA);
        $itemB = (new SetListItem())->setPosition(2)->setName('Mystery')->setSheet($sheetB);

        $setlist = (new Setlist())->setTitle('Mixed');
        $setlist->addItem($itemA);
        $setlist->addItem($itemB);

        $root = $this->parseXml($this->makeExporter(['Absent' => false])->export($setlist));

        static::assertCount(1, $root->score);
        static::assertSame('Present.pdf', (string) $root->score[0]['path']);

        static::assertCount(1, $root->placeholder);
        static::assertSame('Absent.pdf', (string) $root->placeholder[0]['title']);
    }
}
