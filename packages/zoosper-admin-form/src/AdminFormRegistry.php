<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

use Zoosper\Errors\ZoosperException;

/**
 * Registry for Admin forms discovered across modules.
 */
final class AdminFormRegistry
{
    /** @var array<string, AdminFormDefinition> */
    private array $forms = [];

    public function register(AdminFormDefinition $form): void
    {
        $this->forms[$form->handle] = $form;
    }

    public function has(string $handle): bool
    {
        return isset($this->forms[$handle]);
    }

    public function get(string $handle): AdminFormDefinition
    {
        if (!$this->has($handle)) {
            throw new ZoosperException(
                message: 'Admin form not found: ' . $handle,
                context: 'A request was made to load an admin form that is not registered in the registry.',
                suggestion: 'Check your module config/admin_ui.php or direct AdminFormRegistry::register() calls.',
                details: ['handle' => $handle, 'registered_handles' => array_keys($this->forms)]
            );
        }

        return $this->forms[$handle];
    }

    /** @return array<string, AdminFormDefinition> */
    public function all(): array
    {
        return $this->forms;
    }
}











