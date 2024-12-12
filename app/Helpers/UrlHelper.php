<?php

namespace App\Helpers;

class UrlHelper
{
    /**
     * check URL validity and log error details
     * @param string $url
     * @return bool
     */
    public static function isValid (string $url): bool
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            logger()->info("Incorrect URL format: $url\n");
            return false;
        }
        // Check URL accessibility via curl
        $headers = @get_headers($url);
        if (!$headers || !str_contains($headers[0], '200')) {
            logger()->info("URL `$url` can't be reached.");
            return false;
        }
        return true;
    }
}
