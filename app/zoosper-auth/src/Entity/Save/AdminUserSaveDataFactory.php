<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * Builds an AdminUser save data object from raw submitted form data.
 *
 * The factory intentionally keeps every submitted value available in the
 * EntityDataObject so observers and third-party modules can inspect their own
 * fields. Persisting to the core admin_users table still requires the field to
 * be declared in the AdminUser field registry write map.
 */
final readonly class AdminUserSaveDataFactory
{
    public function __construct(private ?AdminUserFieldRegistryFactory $registryFactory = null)
    {
    }

    /** @param array<string, mixed> $submitted */
    public function fromSubmitted(array $submitted): EntityDataObject
    {
        $data = new EntityDataObject();
        $data->addData($submitted);

        if (array_key_exists('locale', $submitted)) {
            $data->setData('locale', $this->normaliseLocale($submitted['locale']));
        }

        return $data;
    }

    public function registry(): FieldDefinitionRegistry
    {
        return ($this->registryFactory ?? new AdminUserFieldRegistryFactory())->create();
    }

    private function normaliseLocale(mixed $locale): ?string
    {
        if (!is_string($locale)) {
            return null;
        }

        $locale = trim($locale);
        if ($locale === '') {
            return null;
        }

        return preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) === 1 ? $locale : null;
    }
}
