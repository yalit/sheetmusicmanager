<?php

namespace App\Tests\Entity;

use App\Entity\Sheet\Sheet;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SheetUploadValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testNoUploadedFilesPassesValidation(): void
    {
        $sheet = new Sheet();
        $sheet->setTitle('Test');
        $sheet->setUploadedFiles([]);

        $violations = $this->validator->validateProperty($sheet, 'uploadedFiles');

        self::assertCount(0, $violations);
    }

    public function testValidPdfPassesValidation(): void
    {
        $path = __DIR__ . '/../public/uploads/sheets/test.pdf';
        $file = new UploadedFile($path, 'test.pdf', 'application/pdf', null, true);

        $sheet = new Sheet();
        $sheet->setTitle('Test');
        $sheet->setUploadedFiles([$file]);

        $violations = $this->validator->validateProperty($sheet, 'uploadedFiles');

        self::assertCount(0, $violations);
    }

    public function testNonPdfFailsValidation(): void
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'sheet_test_');
        file_put_contents($tmpPath, 'not a pdf');
        $file = new UploadedFile($tmpPath, 'document.txt', 'text/plain', null, true);

        $sheet = new Sheet();
        $sheet->setTitle('Test');
        $sheet->setUploadedFiles([$file]);

        $violations = $this->validator->validateProperty($sheet, 'uploadedFiles');

        self::assertGreaterThanOrEqual(1, count($violations));
        self::assertStringContainsString('PDF', (string) $violations->get(0)->getMessage());

        unlink($tmpPath);
    }
}
