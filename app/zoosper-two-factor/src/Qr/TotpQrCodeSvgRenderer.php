<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Qr;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Renders a local SVG QR code for a TOTP provisioning URI.
 *
 * The provisioning URI contains sensitive TOTP setup material. It must be
 * rendered locally only, never sent to external QR services, never logged, and
 * never stored in audit or email logs.
 */
final readonly class TotpQrCodeSvgRenderer
{
    public function __construct(private int $size = 220)
    {
    }

    /**
     * Determine whether the local QR dependency is available.
     */
    public function isAvailable(): bool
    {
        return class_exists(Writer::class)
            && class_exists(ImageRenderer::class)
            && class_exists(RendererStyle::class)
            && class_exists(SvgImageBackEnd::class);
    }

    /**
     * Render the provisioning URI as inline SVG markup.
     */
    public function render(string $provisioningUri): string
    {
        if (!$this->isAvailable()) {
            return '';
        }

        $renderer = new ImageRenderer(
            new RendererStyle($this->size),
            new SvgImageBackEnd(),
        );

        return (new Writer($renderer))->writeString($provisioningUri);
    }
}
