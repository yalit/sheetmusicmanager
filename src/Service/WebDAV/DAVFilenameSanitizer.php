<?php

namespace App\Service\WebDAV;

/**
 * Sanitizes strings to safe, cross-OS WebDAV filenames.
 *
 * Rules applied:
 *  - Strip characters forbidden on Windows/macOS/Linux: \ / : * ? " < > |
 *  - Collapse consecutive spaces and trim
 *  - Cap at 255 characters (before extension)
 */
class DAVFilenameSanitizer implements StringSanitizer
{
    public function sanitize(string $name): string
    {
        $name = preg_replace('/[\\\\\\/:*?"<>|]/', '', $name) ?? $name;
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);

        return mb_substr($name, 0, 255);
    }
}
