<?php

class ImageHelper
{
    public static function uploadUrl($filename, $fallback = '')
    {
        $filename = trim((string) $filename);
        if ($filename === '') {
            return $fallback;
        }

        $filename = basename($filename);
        $relativePath = 'assets/uploads/' . $filename;
        $absolutePath = ROOT_PATH . $relativePath;

        if (is_file($absolutePath)) {
            return BASE_URL . $relativePath;
        }

        return $fallback;
    }

    public static function settingsImageUrl($url, $fallback = '')
    {
        $url = trim((string) $url);
        if ($url === '') {
            return $fallback;
        }

        if (strpos($url, BASE_URL) === 0) {
            $relativePath = ltrim(substr($url, strlen(BASE_URL)), '/');
            $absolutePath = ROOT_PATH . $relativePath;
            return is_file($absolutePath) ? $url : $fallback;
        }

        $parsed = parse_url($url, PHP_URL_PATH);
        if (!$parsed) {
            return $fallback;
        }

        $relativePath = ltrim(str_replace('/Ecom-CMS/', '', $parsed), '/');
        $absolutePath = ROOT_PATH . $relativePath;

        if (is_file($absolutePath)) {
            return BASE_URL . $relativePath;
        }

        return $fallback;
    }

    public static function attrs(array $attributes)
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }

            if ($value === true) {
                $parts[] = htmlspecialchars((string) $key, ENT_QUOTES);
                continue;
            }

            $parts[] = htmlspecialchars((string) $key, ENT_QUOTES) . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
        }

        return implode(' ', $parts);
    }
}
