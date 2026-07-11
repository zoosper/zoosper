<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

use Zoosper\Core\Exception\ZoosperException;

/**
 * Registry of available admin content editors.
 *
 * Custom modules can replace or add editors by overriding this service or by
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

    public function preferred(string $preferred, string $fallback): ContentEditorInterface
    {
        return $this->editors[$preferred] ?? $this->get($fallback);
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_keys($this->editors);
    }
}
