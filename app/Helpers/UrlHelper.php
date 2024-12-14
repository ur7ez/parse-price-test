<?php

namespace App\Helpers;

class UrlHelper
{
    /**
     * check URL validity, return response code if available, and log error details
     * @param string $url
     * @param int|null $response_code - response code for checked URL
     * @return bool
     */
    public static function isValid (string $url, ?int &$response_code): bool
    {
        $response_code = null;
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            logger()->info("Incorrect URL format: $url");
            return false;
        }
        // Check URL accessibility
        $headers = @get_headers($url);
        if (!$headers || !is_array($headers)) {
            logger()->info("URL `$url` can't be reached (no headers returned).");
            return false;
        }
        $response_code = (int)substr($headers[0], 9, 3);
        if ($response_code !== 200) {
            logger()->info("URL `$url` can't be reached. Response code: $response_code");
            return false;
        }
        return true;
    }
}
