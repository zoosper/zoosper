<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController;
use Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;

return [
    AdminTwoFactorSetupController::class => static function (ServiceContainer $services): AdminTwoFactorSetupController {
        $adminConfig = $services->get(ConfigRepository::class)->array('admin');
        $adminBasePath = (string) ($adminConfig['base_path'] ?? '/admin');

        return new AdminTwoFactorSetupController(
            $services->get(SessionGuard::class),
            $services->get(CsrfTokenManager::class),
            $services->get(AdminLayout::class),
            $services->get(AdminTwoFactorEnrollmentService::class),
            $services->get(TotpQrCodeSvgRenderer::class),
            $adminBasePath,
        );
    },
];
