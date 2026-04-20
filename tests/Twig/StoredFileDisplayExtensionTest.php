<?php

namespace App\Tests\Twig;

use App\Entity\Sheet\ValueObject\StoredFile;
use App\Storage\StoredFileStorage;
use App\Twig\StoredFileDisplayExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class StoredFileDisplayExtensionTest extends TestCase
{
    private string $uploadDir;
    private StoredFileStorage $storage;
    private StoredFileDisplayExtension $ext;

    protected function setUp(): void
    {
        // StoredFileStorage is final — instantiate it directly with a real temp dir.
        $this->uploadDir = sys_get_temp_dir() . '/sftest_uploads_' . uniqid();
        mkdir($this->uploadDir, 0777, true);

        // absolutePath() = projectDir / uploadDir / filename
        // Using '.' as projectDir and the full path as uploadDir keeps it simple.
        $this->storage = new StoredFileStorage(
            projectDir: '',
            uploadDir: $this->uploadDir,
            filesystem: new Filesystem(),
        );
        $this->ext = new StoredFileDisplayExtension($this->storage);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->uploadDir);
    }

    private function fileWithContent(string $filename, int $bytes): StoredFile
    {
        file_put_contents($this->uploadDir . DIRECTORY_SEPARATOR . $filename, str_repeat('x', $bytes));

        return new StoredFile($filename, $filename);
    }

    // -------------------------------------------------------------------------
    // fileSize()
    // -------------------------------------------------------------------------

    public function testFileSizeInBytes(): void
    {
        static::assertSame('500 o', $this->ext->fileSize($this->fileWithContent('a.pdf', 500)));
    }

    public function testFileSizeInKilobytes(): void
    {
        static::assertSame('1.5 Ko', $this->ext->fileSize($this->fileWithContent('b.pdf', 1536)));
    }

    public function testFileSizeInMegabytes(): void
    {
        static::assertSame('2 Mo', $this->ext->fileSize($this->fileWithContent('c.pdf', 2 * 1024 * 1024)));
    }

    // -------------------------------------------------------------------------
    // webpath()
    // -------------------------------------------------------------------------

    public function testWebpathStripsPublicFromUploadDir(): void
    {
        $storage = new StoredFileStorage(
            projectDir: '',
            uploadDir: 'public/uploads',
            filesystem: new Filesystem(),
        );
        $ext  = new StoredFileDisplayExtension($storage);
        $file = new StoredFile('doc.pdf', 'doc.pdf');

        static::assertSame('/uploads/doc.pdf', $ext->webpath($file));
    }
}
