<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

use Zoosper\Core\Editor\EditorImageBlockSanitizerInterface;
use Zoosper\Core\Editor\EditorImageBlockValidatorInterface;

/**
 * Normalises stored Editor.js image block data before frontend rendering.
 *
 * This does not render HTML by itself. It prepares a safe, predictable structure
 * that the page renderer can consume in the follow-up wiring step.
 */
final readonly class EditorJsImageBlockSanitizer implements EditorImageBlockSanitizerInterface, EditorImageBlockValidatorInterface
{
    /**
     * @param array<string, mixed> $data
     * @return array{url: string, caption: string, withBorder: bool, withBackground: bool, stretched: bool}|null
     */
    public function sanitise(array $data): ?array
    {
        $file = $data['file'] ?? null;
        if (!is_array($file)) {
            return null;
        }

        $url = trim((string) ($file['url'] ?? ''));
        if ($url === '' || !$this->isAllowedUrl($url)) {
            return null;
        }

        return [
            'url' => $url,
            'caption' => trim((string) ($data['caption'] ?? '')),
            'withBorder' => (bool) ($data['withBorder'] ?? false),
            'withBackground' => (bool) ($data['withBackground'] ?? false),
            'stretched' => (bool) ($data['stretched'] ?? false),
        ];
    }

    private function isAllowedUrl(string $url): bool
    {
        return str_starts_with($url, '/media/');
    }
    /** @return list<string> */
    public function validate(array $data): array
    {
        $errors = [];
        $file = $data['file'] ?? null;
        if (!is_array($file)) {
            return ['image file must be an object.'];
        }

        $url = trim((string) ($file['url'] ?? ''));
        if ($url === '' || !str_starts_with($url, '/media/')) {
            $errors[] = 'image URL must use managed /media/ storage.';
        }
        if (isset($data['caption']) && !is_string($data['caption'])) {
            $errors[] = 'image caption must be a string.';
        }
        foreach (['withBorder', 'withBackground', 'stretched'] as $flag) {
            if (isset($data[$flag]) && !is_bool($data[$flag])) {
                $errors[] = 'image flag ' . $flag . ' must be boolean.';
            }
        }

        return $errors;
    }

}











