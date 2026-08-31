<?php

declare(strict_types=1);

namespace Zoosper\Settings\Controller;

use RuntimeException;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Settings\Admin\SettingsCatalogueResponder;
use Zoosper\Settings\Admin\SettingsMutationCoordinator;

/** Thin authenticated HTTP adapter for the Settings Admin workspace. */
final readonly class SettingsCatalogueController
{
    public function __construct(
        private SessionGuard $guard,
        private SettingsCatalogueResponder $catalogue,
        private SettingsMutationCoordinator $mutations,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->catalogue->respond($request, $this->currentAdminUser());
    }

    public function save(Request $request): Response
    {
        return $this->mutations->save($request, $this->currentAdminUser());
    }

    public function clear(Request $request): Response
    {
        return $this->mutations->clear($request, $this->currentAdminUser());
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}










