<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use Zoosper\Auth\Model\AdminUser;

final readonly class DashboardPersonalisationService
{
    public function __construct(
        private ModuleDashboardWidgetLoader $widgets,
        private DashboardPreferenceRepository $preferences,
        private DashboardWidgetPersonaliser $personaliser,
    ) {
    }

    public function forUser(AdminUser $user): PersonalisedDashboardWidgetCollection
    {
        $permitted = $this->widgets->forUser($user);

        return $this->personaliser->apply($permitted, $this->preferences->findForUser($user->id));
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
}
