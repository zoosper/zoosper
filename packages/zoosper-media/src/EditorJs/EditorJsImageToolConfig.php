<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

/**
 * Builds the client-side configuration payload for the Editor.js Image Tool.
 *
 * This is intentionally pure and framework-light so the admin editor can consume
 * it from PHP-rendered templates or future JSON endpoints without duplicating the
 * endpoint, field-name or CSRF-header conventions.
 */
final readonly class EditorJsImageToolConfig
{
    public function __construct(
        private string $uploadEndpoint = '/admin/media/editorjs/upload',
        private string $fieldName = 'image',
        private string $acceptedTypes = 'image/*',
        private string $csrfHeader = 'X-CSRF-Token',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $csrfToken): array
    {
        return [
            'endpoints' => [
                'byFile' => $this->uploadEndpoint,
            ],
            'field' => $this->fieldName,
            'types' => $this->acceptedTypes,
            'additionalRequestHeaders' => [
                $this->csrfHeader => $csrfToken,
            ],
            'features' => [
                'caption' => true,
                'border' => true,
                'background' => true,
                'stretch' => true,
            ],
        ];
    }
}
