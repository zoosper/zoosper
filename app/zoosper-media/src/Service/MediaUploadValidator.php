<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Validates an uploaded media file before it is persisted or published.
 *
 * The first media foundation intentionally accepts browser-safe raster/image
 * types only. Executable formats, SVG, archives and documents are rejected until
 * dedicated sanitisation and delivery policies exist.
 */
final readonly class MediaUploadValidator
{
    private const MAX_BYTES = 5_242_880;

    /** @var array<string, list<string>> */
    private const ALLOWED = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
    ];

    /**
     * @param array<string, mixed> $file One $_FILES entry.
     */
    public function validate(array $file): MediaUploadValidationResult
    {
        $errors = [];
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            return MediaUploadValidationResult::fail(['Upload failed or no file was selected.']);
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_file($tmpName)) {
            return MediaUploadValidationResult::fail(['Uploaded temporary file is missing.']);
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!array_key_exists($extension, self::ALLOWED)) {
            $errors[] = 'Unsupported media file extension.';
        }

        $size = (int) ($file['size'] ?? filesize($tmpName) ?: 0);
        if ($size <= 0) {
            $errors[] = 'Uploaded file is empty.';
        }
        if ($size > self::MAX_BYTES) {
            $errors[] = 'Uploaded file exceeds the 5 MB limit.';
        }

        $mimeType = $this->detectMimeType($tmpName);
        if ($extension !== '' && !in_array($mimeType, self::ALLOWED[$extension] ?? [], true)) {
            $errors[] = 'Uploaded file MIME type does not match the extension.';
        }

        if (!$this->looksLikeImage($tmpName)) {
            $errors[] = 'Uploaded file is not a valid image.';
        }

        return $errors === []
            ? MediaUploadValidationResult::ok($extension, $mimeType, $size)
            : MediaUploadValidationResult::fail($errors);
    }

    private function detectMimeType(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return 'application/octet-stream';
        }

        /**
         * PHP 8.5 deprecates finfo_close(); finfo objects are released
         * automatically, so intentionally do not close it manually.
         */
        return (string) finfo_file($finfo, $path);
    }

    private function looksLikeImage(string $path): bool
    {
        return @getimagesize($path) !== false;
    }
}
