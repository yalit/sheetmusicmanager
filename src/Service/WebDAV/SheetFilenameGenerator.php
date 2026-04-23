<?php

namespace App\Service\WebDAV;

use App\Entity\Sheet\Sheet;

/**
 * @template-implements NameGenerator<Sheet>
 */
readonly class SheetFilenameGenerator implements NameGenerator
{

    public function __construct(
        private StringSanitizer $stringSanitizer,
    ) {}

    /**
     * @param Sheet $data
     * @return string
     */
    public function generate(mixed $data): string
    {
        return $this->stringSanitizer->sanitize($data->getTitle()).'.pdf';
    }
}
