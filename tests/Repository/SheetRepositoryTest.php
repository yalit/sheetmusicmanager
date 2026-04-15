<?php

namespace App\Tests\Repository;

use App\Repository\SheetRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SheetRepositoryTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    private function repository(): SheetRepository
    {
        return static::getContainer()->get(SheetRepository::class);
    }

    public function testGetAllRefs(): void
    {
        $refs = $this->repository()->getAllRefs();
        $this->assertIsArray($refs);
        self::assertCount(13, $refs);
    }

    public function testGetAllTags(): void
    {
        $tags = $this->repository()->getAllTags();
        $this->assertIsArray($tags);
        self::assertCount(13, $tags);
    }

    public function testGetUntagged(): void
    {
        $untagged = $this->repository()->findUntagged();
        $this->assertIsArray($untagged);
        self::assertNotEmpty($untagged);
        self::assertCount(1, $untagged);

        $sheet = $untagged[0];
        self::assertSame('Untagged', $sheet->getTitle());
    }

    public static function findByTagProvider(): iterable
    {
        yield "Only one" => ['unique_tag', 1];
        yield "Multiple" => ['piano', 5];
    }

    #[DataProvider('findByTagProvider')]
    public function testFindByTag(string $tag, int $expectedNbSheets): void
    {
        $sheets = $this->repository()->findByTag($tag);
        $this->assertIsArray($sheets);
        self::assertCount($expectedNbSheets, $sheets);
    }
}
