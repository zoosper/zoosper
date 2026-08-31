<?php

declare(strict_types=1);

namespace Zoosper\Editor;

use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Errors\ZoosperException;

/**
 * Registry of available content editor implementations.
 *
 * Provides a fallback-safe selection mechanism so admin authoring continues to
 * work even when a third-party or rich editor is disabled, uninstalled or
 * failing. Third-party editor modules register themselves at runtime by
 * contributing a different default editor through config/services.php.
 */
final class ContentEditorRegistry
{
    /** @var array<string, ContentEditorInterface> */
    private array $editors = [];

    public function __construct(ContentEditorInterface ...$editors)
    {
        foreach ($editors as $editor) {
            $this->register($editor);
        }
    }

    public function register(ContentEditorInterface $editor): void
    {
        $this->editors[$editor->code()] = $editor;
    }

    public function get(string $code): ContentEditorInterface
    {
        if (!isset($this->editors[$code])) {
            throw new ZoosperException(
                message: 'Content editor is not registered: ' . $code,
                context: 'An admin form requested a content editor code that no enabled module registered.',
                suggestion: 'Register the editor in a module service provider or set CONTENT_EDITOR to an available editor such as textarea or editorjs.',
                docsUrl: 'docs/operations/content-editor-testing.md',
                details: ['editor_code' => $code, 'available_editors' => array_keys($this->editors)],
            );
        }

        return $this->editors[$code];
    }

    /**
     * Choose the preferred editor if available, otherwise fall back.
     */
    public function preferred(string $preferredCode, string $fallbackCode = 'textarea'): ContentEditorInterface
    {
        return $this->editors[$preferredCode] ?? $this->get($fallbackCode);
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_keys($this->editors);
    }

    /** @return array<string, ContentEditorInterface> */
    public function all(): array
    {
        return $this->editors;
    }
}










