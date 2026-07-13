<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\FieldDefinition;
use Zoosper\Core\Entity\Save\FieldDefinitionProviderInterface;
use Zoosper\Core\Entity\Save\FieldStorageType;

/**
 * Declares the core AdminUser fields that may be written by save pipelines.
 *
 * This provider is the first concrete use of the generic entity save pipeline.
 * It turns admin-user form values such as locale into explicit field
 * definitions, so the repository can save only known admin_users columns while
 * third-party module values remain available for extension persistence later.
 */
final readonly class AdminUserFieldDefinitionProvider implements FieldDefinitionProviderInterface
{
    public function definitions(): iterable
    {
        return [
            FieldDefinition::coreColumn('name', 'Name'),
            FieldDefinition::coreColumn('email', 'Email', required: true),
            FieldDefinition::coreColumn('status', 'Status'),
            FieldDefinition::coreColumn('locale', 'Admin interface locale'),
            new FieldDefinition(
                name: 'password',
                label: 'Password',
                storageType: FieldStorageType::Handler,
                required: false,
            ),
            new FieldDefinition(
                name: 'role_ids',
                label: 'Roles',
                storageType: FieldStorageType::Handler,
                required: false,
            ),
            FieldDefinition::virtual('csrf_token', 'CSRF token'),
        ];
    }
}
