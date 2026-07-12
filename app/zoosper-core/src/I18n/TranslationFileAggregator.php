<?php

declare(strict_types=1);

namespace Zoosper\Core\I18n;

/**
 * Aggregates module-owned translation files for a locale.
 *
 * Supported paths are deliberately module-friendly. The examples below avoid
 * literal wildcard path syntax so this PHPDoc block cannot be accidentally
 * closed by a slash-star sequence inside an example path.
 *
 * Supported locations:
 * - application module i18n directories
 * - first-level community module i18n directories
 * - vendor/community module i18n directories
 * - Composer vendor package i18n directories
 * - project-level config i18n directories
 *
 * Files must return array<string, string>. Later files override earlier files,
 * allowing project-level config to customise module copy without editing core.
 */
final readonly class TranslationFileAggregator
{
    public function __construct(private string $basePath)
    {
    }

    public function catalogue(string $locale, string $fallbackLocale = 'en_AU'): TranslationCatalogue
    {
        $messages = [];

        if ($fallbackLocale !== $locale) {
            $messages = $this->mergeLocale($messages, $fallbackLocale);
        }

        $messages = $this->mergeLocale($messages, $locale);

        return new TranslationCatalogue($locale, $messages);
    }

    /**
     * @param array<string, string> $messages
     *
     * @return array<string, string>
     */
    private function mergeLocale(array $messages, string $locale): array
    {
        foreach ($this->translationFiles($locale) as $file) {
            $messages = array_replace($messages, $this->readTranslationFile($file));
        }

        return $messages;
    }

    /** @return list<string> */
    private function translationFiles(string $locale): array
    {
        $filename = $locale . '.php';
        $patterns = [
            $this->basePath . '/app/*/i18n/' . $filename,
            $this->basePath . '/modules/*/i18n/' . $filename,
            $this->basePath . '/modules/*/*/i18n/' . $filename,
            $this->basePath . '/vendor/*/*/i18n/' . $filename,
            $this->basePath . '/config/i18n/' . $filename,
        ];

        $files = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file)) {
                    $files[] = $file;
                }
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    /** @return array<string, string> */
    private function readTranslationFile(string $file): array
    {
        $translations = require $file;
        if (!is_array($translations)) {
            return [];
        }

        $normalised = [];
        foreach ($translations as $source => $translated) {
            if (!is_string($source) || !is_string($translated)) {
                continue;
            }

            $normalised[$source] = $translated;
        }

        return $normalised;
    }
}
