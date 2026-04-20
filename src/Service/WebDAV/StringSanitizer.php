<?php

namespace App\Service\WebDAV;

interface StringSanitizer
{
    public function sanitize(string $string): string;
}
