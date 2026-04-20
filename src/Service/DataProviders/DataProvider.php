<?php

namespace App\Service\DataProviders;

/**
 * @template D of object
 */
interface DataProvider
{
    /**
     * @param D $data
     */
    public function getContent($data): string;

    /**
     * @param D $data
     */
    public function getSize($data): ?int;
}
