<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

/**
 * Normalises stored Editor.js image block data before frontend rendering.
 *
 * This does not render HTML by itself. It prepares a safe, predictable structure
 * that the page renderer can consume in the follow-up wiring step.
 */
final readonly class EditorJsImageBlockSanitizer
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
}
