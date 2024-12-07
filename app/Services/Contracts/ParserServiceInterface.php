<?php

namespace App\Services\Contracts;

interface ParserServiceInterface
{
    /**
     * Parse prices from the given URLs.
     *
     * @param array $urls
     * @return array
     */
    public function parsePrice(array $urls): array;
    public function getAdData(): array;
}
