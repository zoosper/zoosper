<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

use RuntimeException;

/**
 * Runtime registry for admin form section providers.
 *
 * Phase 1.41 (page decoupling, part A): relocated to Zoosper\AdminForm —
 * see AdminFormSection.php for the full reasoning. Logic is completely
 * unchanged.
 */
final class AdminFormProviderRegistry
{
    /** @var list<AdminFormSectionProviderInterface> */
    private array $providers = [];

    public function add(AdminFormSectionProviderInterface $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return list<AdminFormSection>
     */
    public function sectionsFor(string $formHandle, array $context): array
    {
        $sections = [];
        foreach ($this->providers as $provider) {
            if ($provider->formHandle() !== $formHandle) {
                continue;
            }

            foreach ($provider->sections($context) as $section) {
                if (isset($sections[$section->key])) {
                    throw new RuntimeException('Duplicate admin form section key: ' . $section->key);
                }

                $sections[$section->key] = $section;
            }
        }

        uasort($sections, static function (AdminFormSection $left, AdminFormSection $right): int {
            return $left->sortOrder <=> $right->sortOrder ?: strcmp($left->key, $right->key);
        });

        return array_values($sections);
    }
}

