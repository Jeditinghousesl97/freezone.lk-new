<?php

class ImageHelper
{
    private const DERIVED_DIR = 'assets/uploads/derived/';
    private const ORIGINAL_DIR = 'assets/uploads/';
    private const QUALITY_WEBP = 82;
    private const QUALITY_AVIF = 52;
    private const QUALITY_JPEG = 84;

    public static function uploadUrl($filename, $fallback = '')
    {
        $filename = trim((string) $filename);
        if ($filename === '') {
            return $fallback;
        }

        $filename = basename($filename);
        $relativePath = self::ORIGINAL_DIR . $filename;
        $absolutePath = ROOT_PATH . $relativePath;

        return is_file($absolutePath) ? BASE_URL . $relativePath : $fallback;
    }

    public static function settingsImageUrl($url, $fallback = '')
    {
        $url = trim((string) $url);
        if ($url === '') {
            return $fallback;
        }

        if (strpos($url, BASE_URL) === 0) {
            $relativePath = ltrim(substr($url, strlen(BASE_URL)), '/');
            return is_file(ROOT_PATH . $relativePath) ? $url : $fallback;
        }

        $parsed = parse_url($url, PHP_URL_PATH);
        if (!$parsed) {
            return $fallback;
        }

        $relativePath = ltrim(str_replace('/Ecom-CMS/', '', $parsed), '/');
        return is_file(ROOT_PATH . $relativePath) ? BASE_URL . $relativePath : $fallback;
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

    public static function storeUploadedFile(array $file, $prefix = 'img')
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            return '';
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        $originalName = (string) ($file['name'] ?? 'image');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return '';
        }

        self::ensureDirectory(ROOT_PATH . self::ORIGINAL_DIR);
        self::ensureDirectory(ROOT_PATH . self::DERIVED_DIR);

        $baseName = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', basename($originalName));
        $targetPath = ROOT_PATH . self::ORIGINAL_DIR . $baseName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '';
        }

        self::generateDerivativeSet($baseName);
        return $baseName;
    }

    public static function storeUploadedArrayFile(array $files, $index, $prefix = 'img')
    {
        if (!isset($files['name'][$index])) {
            return '';
        }

        $file = [
            'name' => $files['name'][$index] ?? '',
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0
        ];

        return self::storeUploadedFile($file, $prefix);
    }

    public static function deleteImageSet($filename)
    {
        $filename = basename(trim((string) $filename));
        if ($filename === '') {
            return;
        }

        $originalPath = ROOT_PATH . self::ORIGINAL_DIR . $filename;
        if (is_file($originalPath)) {
            @unlink($originalPath);
        }

        $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $derivedDir = ROOT_PATH . self::DERIVED_DIR;
        if (!is_dir($derivedDir)) {
            return;
        }

        foreach (glob($derivedDir . $nameWithoutExtension . '__*') ?: [] as $derivedFile) {
            if (is_file($derivedFile)) {
                @unlink($derivedFile);
            }
        }
    }

    public static function getOptimizableUploads()
    {
        $originalDir = ROOT_PATH . self::ORIGINAL_DIR;
        if (!is_dir($originalDir)) {
            return [];
        }

        $entries = scandir($originalDir) ?: [];
        $files = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $absolutePath = $originalDir . $entry;
            if (!is_file($absolutePath)) {
                continue;
            }

            if (!self::isOptimizableImage($entry)) {
                continue;
            }

            $files[] = $entry;
        }

        sort($files, SORT_NATURAL | SORT_FLAG_CASE);
        return $files;
    }

    public static function optimizeExistingUploadsBatch($force = false, $limit = 25, $offset = 0)
    {
        self::ensureDirectory(ROOT_PATH . self::DERIVED_DIR);
        $files = self::getOptimizableUploads();
        $total = count($files);
        $offset = max(0, (int) $offset);
        $limit = max(1, (int) $limit);

        $batchFiles = array_slice($files, $offset, $limit);
        $result = [
            'scanned' => count($batchFiles),
            'optimized' => 0,
            'skipped' => 0,
            'failed' => 0,
            'formats' => [],
            'files' => [],
            'offset' => $offset,
            'next_offset' => min($offset + count($batchFiles), $total),
            'limit' => $limit,
            'total' => $total,
            'complete' => ($offset + count($batchFiles)) >= $total
        ];

        foreach ($batchFiles as $entry) {
            $beforeFiles = self::derivedFilesFor($entry);
            $beforeCount = count($beforeFiles);

            if ($force) {
                foreach ($beforeFiles as $derivedFile) {
                    if (is_file($derivedFile)) {
                        @unlink($derivedFile);
                    }
                }
            }

            $generated = self::generateDerivativeSet($entry);
            $afterFiles = self::derivedFilesFor($entry);
            $afterCount = count($afterFiles);

            if ($generated === false || $afterCount === 0) {
                $result['failed']++;
                continue;
            }

            if ($force || $afterCount > $beforeCount) {
                $result['optimized']++;
                foreach ($afterFiles as $afterFile) {
                    $ext = strtolower((string) pathinfo($afterFile, PATHINFO_EXTENSION));
                    if ($ext !== '') {
                        $result['formats'][$ext] = ($result['formats'][$ext] ?? 0) + 1;
                    }
                }
                if (count($result['files']) < 20) {
                    $result['files'][] = $entry;
                }
            } else {
                $result['skipped']++;
            }
        }

        ksort($result['formats']);
        return $result;
    }

    public static function imageDelivery($filename, $fallback = '', $profile = 'default')
    {
        $fallbackUrl = self::uploadUrl($filename, $fallback);
        $filename = basename(trim((string) $filename));
        if ($filename === '' || $fallbackUrl === $fallback) {
            return [
                'src' => $fallbackUrl,
                'sources' => [],
                'fallback' => $fallbackUrl
            ];
        }

        $profileConfig = self::profileConfig($profile);
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $sources = [];

        foreach (['avif', 'webp'] as $format) {
            $srcsetParts = [];
            foreach ($profileConfig['widths'] as $width) {
                $derivedRelative = self::DERIVED_DIR . $baseName . '__' . $width . '.' . $format;
                $derivedAbsolute = ROOT_PATH . $derivedRelative;
                if (is_file($derivedAbsolute)) {
                    $srcsetParts[] = BASE_URL . $derivedRelative . ' ' . $width . 'w';
                }
            }

            if (!empty($srcsetParts)) {
                $sources[] = [
                    'type' => 'image/' . $format,
                    'srcset' => implode(', ', $srcsetParts),
                    'sizes' => $profileConfig['sizes']
                ];
            }
        }

        return [
            'src' => $fallbackUrl,
            'sources' => $sources,
            'fallback' => $fallbackUrl
        ];
    }

    public static function renderResponsivePicture($filename, $fallback, array $attributes = [], $profile = 'default')
    {
        $delivery = self::imageDelivery($filename, $fallback, $profile);
        $html = '<picture>';

        foreach ($delivery['sources'] as $source) {
            $html .= '<source ' . self::attrs([
                'type' => $source['type'],
                'srcset' => $source['srcset'],
                'sizes' => $source['sizes']
            ]) . '>';
        }

        $attributes = array_merge(['src' => $delivery['src']], $attributes);
        $html .= '<img ' . self::attrs($attributes) . '>';
        $html .= '</picture>';

        return $html;
    }

    private static function ensureDirectory($path)
    {
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
    }

    private static function profileConfig($profile)
    {
        $profiles = [
            'product_card' => ['widths' => [320, 640], 'sizes' => '(max-width: 768px) 45vw, 268px'],
            'category_card' => ['widths' => [240, 480], 'sizes' => '(max-width: 768px) 45vw, 240px'],
            'product_gallery' => ['widths' => [640, 960, 1440], 'sizes' => '(max-width: 768px) 100vw, 50vw'],
            'hero' => ['widths' => [640, 960, 1440], 'sizes' => '100vw'],
            'logo' => ['widths' => [120, 240], 'sizes' => '120px'],
            'admin_thumb' => ['widths' => [160, 320], 'sizes' => '160px'],
            'feedback' => ['widths' => [480, 960], 'sizes' => '(max-width: 768px) 90vw, 480px'],
            'default' => ['widths' => [320, 640, 960], 'sizes' => '100vw']
        ];

        return $profiles[$profile] ?? $profiles['default'];
    }

    public static function regenerateImageSet($filename, $force = false)
    {
        $filename = basename(trim((string) $filename));
        if ($filename === '' || !self::isOptimizableImage($filename)) {
            return false;
        }

        if ($force) {
            foreach (self::derivedFilesFor($filename) as $derivedFile) {
                if (is_file($derivedFile)) {
                    @unlink($derivedFile);
                }
            }
        }

        return self::generateDerivativeSet($filename);
    }

    private static function generateDerivativeSet($filename)
    {
        $filename = basename(trim((string) $filename));
        if ($filename === '') {
            return false;
        }

        $sourcePath = ROOT_PATH . self::ORIGINAL_DIR . $filename;
        if (!is_file($sourcePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension === 'gif') {
            return false;
        }

        if (extension_loaded('imagick')) {
            return self::generateWithImagick($sourcePath, pathinfo($filename, PATHINFO_FILENAME));
        }

        if (extension_loaded('gd')) {
            return self::generateWithGd($sourcePath, pathinfo($filename, PATHINFO_FILENAME));
        }

        return false;
    }

    private static function generateWithImagick($sourcePath, $baseName)
    {
        try {
            $image = new Imagick($sourcePath);
            if (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            }

            $sourceWidth = max(1, (int) $image->getImageWidth());
            $written = 0;
            foreach ([160, 240, 320, 480, 640, 960, 1440] as $width) {
                $targetWidth = min($width, $sourceWidth);
                foreach (['webp', 'avif'] as $format) {
                    $variantPath = ROOT_PATH . self::DERIVED_DIR . $baseName . '__' . $targetWidth . '.' . $format;
                    if (is_file($variantPath)) {
                        continue;
                    }

                    $clone = clone $image;
                    $clone->thumbnailImage($targetWidth, 0);
                    $clone->setImageFormat($format);
                    $clone->setImageCompressionQuality($format === 'avif' ? self::QUALITY_AVIF : self::QUALITY_WEBP);
                    if ($clone->writeImage($variantPath)) {
                        $written++;
                    }
                    $clone->clear();
                    $clone->destroy();
                }
            }

            $image->clear();
            $image->destroy();
            return $written > 0 || count(self::derivedFilesFor($baseName)) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function generateWithGd($sourcePath, $baseName)
    {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mime = strtolower((string) ($imageInfo['mime'] ?? ''));
        $source = self::gdImageFromFile($sourcePath, $mime);
        if (!$source) {
            return false;
        }

        $sourceWidth = max(1, imagesx($source));
        $sourceHeight = max(1, imagesy($source));

        $written = 0;
        foreach ([160, 240, 320, 480, 640, 960, 1440] as $width) {
            $targetWidth = min($width, $sourceWidth);
            $targetHeight = max(1, (int) round(($targetWidth / $sourceWidth) * $sourceHeight));
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

            $webpPath = ROOT_PATH . self::DERIVED_DIR . $baseName . '__' . $targetWidth . '.webp';
            if (!is_file($webpPath) && function_exists('imagewebp') && @imagewebp($canvas, $webpPath, self::QUALITY_WEBP)) {
                $written++;
            }
            $avifPath = ROOT_PATH . self::DERIVED_DIR . $baseName . '__' . $targetWidth . '.avif';
            if (!is_file($avifPath) && function_exists('imageavif') && @imageavif($canvas, $avifPath, self::QUALITY_AVIF)) {
                $written++;
            }

            imagedestroy($canvas);
        }

        imagedestroy($source);
        return $written > 0;
    }

    private static function derivedFilesFor($filename)
    {
        $filename = basename(trim((string) $filename));
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        if ($baseName === '') {
            return [];
        }

        return glob(ROOT_PATH . self::DERIVED_DIR . $baseName . '__*') ?: [];
    }

    private static function isOptimizableImage($filename)
    {
        $extension = strtolower((string) pathinfo((string) $filename, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'avif'], true);
    }

    private static function gdImageFromFile($path, $mime)
    {
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                return @imagecreatefrompng($path);
            case 'image/gif':
                return @imagecreatefromgif($path);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
            case 'image/avif':
                return function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : null;
            default:
                return null;
        }
    }
}
