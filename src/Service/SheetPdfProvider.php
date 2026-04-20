<?php

namespace App\Service;

use App\Entity\Sheet\Sheet;
use App\Service\DataProviders\DataProvider;
use App\Storage\StoredFileStorage;
use Sabre\DAV\Exception\NotFound;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;

/**
 * @implements DataProvider<Sheet>
 */
readonly class SheetPdfProvider implements DataProvider
{
    public function __construct(
        private StoredFileStorage     $storage,
        private GotenbergPdfInterface $gotenberg,
    ) {}

    /**
     * Returns the PDF content for a sheet as a binary string.
     *
     * - 1 file  : served directly from disk
     * - N files : merged via Gotenberg (same merge as the setlist action)
     *
     *@throws NotFound when no PDF file exists on disk
     */
    public function getContent($data): string
    {
        $paths = array_values(array_filter(
            array_map(
                fn($file) => $this->storage->absolutePath($file),
                $data->getFiles()
            ),
            fn(string $path) => file_exists($path)
        ));

        if ($paths === []) {
            throw new NotFound(sprintf('No PDF file found on disk for sheet "%s"', $data->getTitle()));
        }

        if (count($paths) === 1) {
            return file_get_contents($paths[0]);
        }

        $response = $this->gotenberg->merge()
            ->files(...$paths)
            ->generate()
            ->stream();

        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }

    /**
     * Returns the file size in bytes, or null when the content is merged (size is unknown until merge).
     */
    public function getSize($data): ?int
    {
        $files = $data->getFiles();

        if (count($files) !== 1) {
            return null;
        }

        $path = $this->storage->absolutePath($files[0]);

        return file_exists($path) ? filesize($path) : null;
    }
}
