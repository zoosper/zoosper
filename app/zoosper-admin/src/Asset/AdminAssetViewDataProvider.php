<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

/**
 * Provides admin layout view data for module-owned assets.
 *
 * The provider prepares stylesheet/script collections for template rendering
 * while keeping runtime-sensitive values out of asset payloads. Asset config
 * must remain static and must never contain OTPs, TOTP secrets, recovery-code
 * plaintext, session IDs, payment data, or customer-private data.
 */
final readonly class AdminAssetViewDataProvider
{
    public function __construct(private AdminAssetRegistry $assets)
    {
    }

    /**
     * Return template data for admin asset rendering.
     *
     * @return array{stylesheets:list<AdminAsset>,scripts:list<AdminAsset>}
     */
    public function data(): array
    {
        return [
            'stylesheets' => $this->assets->stylesheets(),
            'scripts' => $this->assets->scripts(),
        ];
    }
}
