<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use RuntimeException;
use Zoosper\AdminDashboard\Contract\DashboardRolePreferenceRepositoryInterface;
use Zoosper\AdminDashboard\DashboardRole;
use Zoosper\Auth\Model\AdminUser;

final readonly class DashboardPersonalisationService
{
    public function __construct(
        private ModuleDashboardWidgetLoader $widgets,
        private DashboardPreferenceRepository $preferences,
        private DashboardWidgetPersonaliser $personaliser,
        private ?DashboardRolePreferenceRepositoryInterface $rolePreferences = null,
    ) {
    }

    public function forUser(AdminUser $user): PersonalisedDashboardWidgetCollection
    {
        $permitted = $this->widgets->forUser($user);
        $preference = $this->preferences->findForUser($user->id);
        $rolePreferences = $preference === null && $this->rolePreferences !== null
            ? $this->rolePreferences->findForUser($user->id)
            : [];

        return $this->personaliser->apply($permitted, $preference, $rolePreferences);
    }

    /** @return list<DashboardRole> */
    public function roles(): array
    {
        return $this->roleRepository()->roles();
    }

    public function forRoleEditor(AdminUser $actor, int $roleId): PersonalisedDashboardWidgetCollection
    {
        $role = $this->role($roleId);
        $permitted = $this->widgets->forUser($actor);
        $preference = $this->roleRepository()->findForRole($role->id);

        return $this->personaliser->apply(
            $permitted,
            $preference === null ? null : new DashboardPreference($preference->hiddenWidgetCodes, $preference->widgetOrder),
        );
    }

    public function saveRoleDefault(
        AdminUser $actor,
        int $roleId,
        mixed $knownWidgetCodes,
        mixed $visibleWidgetCodes,
        mixed $widgetOrder,
    ): void {
        $role = $this->role($roleId);
        $permitted = $this->widgets->forUser($actor);
        $submitted = $this->personaliser->preferenceFromSubmission(
            $permitted->widgets,
            $knownWidgetCodes,
            $visibleWidgetCodes,
            $widgetOrder,
        );

        $repository = $this->roleRepository();
        $existing = $repository->findForRole($role->id);
        $allowed = array_fill_keys(array_column($permitted->widgets, 'code'), true);
        $hidden = $submitted->hiddenWidgetCodes;
        if ($existing !== null) {
            foreach ($existing->hiddenWidgetCodes as $code) {
                if (!isset($allowed[$code])) {
                    $hidden[] = $code;
                }
            }
        }

        $order = $this->mergeAuthorisedOrder(
            $existing?->widgetOrder ?? [],
            $submitted->widgetOrder,
            $allowed,
        );
        $repository->saveForRole($role->id, array_values(array_unique($hidden)), $order);
    }

    public function resetRoleDefault(int $roleId): void
    {
        $this->roleRepository()->clearForRole($this->role($roleId)->id);
    }

    public function saveForUser(
        AdminUser $user,
        mixed $knownWidgetCodes,
        mixed $visibleWidgetCodes,
        mixed $widgetOrder,
    ): void {
        $permitted = $this->widgets->forUser($user);
        $preference = $this->personaliser->preferenceFromSubmission(
            $permitted->widgets,
            $knownWidgetCodes,
            $visibleWidgetCodes,
            $widgetOrder,
        );
        $this->preferences->saveForUser($user->id, $preference);
    }

    public function resetForUser(AdminUser $user): void
    {
        $this->preferences->clearForUser($user->id);
    }

    private function roleRepository(): DashboardRolePreferenceRepositoryInterface
    {
        return $this->rolePreferences
            ?? throw new RuntimeException('Dashboard role defaults are unavailable.');
    }

    private function role(int $roleId): DashboardRole
    {
        foreach ($this->roleRepository()->roles() as $role) {
            if ($role->id === $roleId) {
                return $role;
            }
        }

        throw new RuntimeException('Dashboard role does not exist.');
    }

    /**
     * @param list<string> $existing
     * @param list<string> $submitted
     * @param array<string, true> $allowed
     * @return list<string>
     */
    private function mergeAuthorisedOrder(array $existing, array $submitted, array $allowed): array
    {
        $remaining = $submitted;
        $merged = [];
        foreach ($existing as $code) {
            if (isset($allowed[$code])) {
                $replacement = array_shift($remaining);
                if (is_string($replacement)) {
                    $merged[] = $replacement;
                }
                continue;
            }
            $merged[] = $code;
        }

        return array_values(array_unique([...$merged, ...$remaining]));
    }
}










