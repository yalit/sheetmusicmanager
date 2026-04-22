<?php

namespace App\Service\WebDAV;

/**
 * @template T of object
 */
interface NameGenerator
{
    public function generate(T $data): string;
}
