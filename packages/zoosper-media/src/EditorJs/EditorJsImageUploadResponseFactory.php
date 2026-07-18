<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

/**
 * Builds the response payload expected by the Editor.js Image Tool.
 *
 * The Image Tool expects a JSON object containing success=1 and a file object
 * with a url field after an upload succeeds. Keeping that shape here gives the
 * media module one canonical response contract before controller/API wiring is
 * added.
 */
final readonly class EditorJsImageUploadResponseFactory
{
    /**
     * @param array<string, scalar|null> $extra
     * @return array{success: int, file: array<string, scalar|null>}
     */
    public function success(string $url, array $extra = []): array
    {
        $file = ['url' => $url];
        foreach ($extra as $key => $value) {
            if (is_string($key) && $key !== '') {
                $file[$key] = $value;
            }
        }

        return [
            'success' => 1,
            'file' => $file,
        ];
    }

    /**
     * @return array{success: int, message: string}
     */
    public function failure(string $message): array
    {
        return [
            'success' => 0,
            'message' => $message,
        ];
    }
}
