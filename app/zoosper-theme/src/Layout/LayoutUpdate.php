<?php

declare(strict_types=1);

namespace Zoosper\Theme\Layout;

final readonly class LayoutUpdate
{
    /**
     * @param list<string> $remove
     * @param array<string, string> $replace
     * @param array<string, list<string>> $inject
     */
    public function __construct(
        private array $remove = [],
        private array $replace = [],
        private array $inject = [],
    ) {
    }

    /** @param list<array<string, mixed>> $updates */
    public static function merge(array $updates): self
    {
        $remove = [];
        $replace = [];
        $inject = [];

        foreach ($updates as $update) {
            if (!is_array($update)) {
                continue;
            }
            foreach (($update['remove'] ?? []) as $template) {
                $remove[] = (string) $template;
            }
            foreach (($update['replace'] ?? []) as $from => $to) {
                $replace[(string) $from] = (string) $to;
            }
            foreach (($update['inject'] ?? []) as $slot => $templates) {
                foreach ((array) $templates as $template) {
                    $inject[(string) $slot][] = (string) $template;
                }
            }
        }

        return new self(array_values(array_unique($remove)), $replace, $inject);
    }

    public function isRemoved(string $template): bool
    {
        return in_array($template, $this->remove, true);
    }

    public function replacementFor(string $template): ?string
    {
        return $this->replace[$template] ?? null;
    }

    /** @return list<string> */
    public function injectionsFor(string $slot): array
    {
        return $this->inject[$slot] ?? [];
    }
}
