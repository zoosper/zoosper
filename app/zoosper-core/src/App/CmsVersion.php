<?php

declare(strict_types=1);

namespace Zoosper\Core\App;

use Zoosper\Core\Config\ConfigRepository;

final readonly class CmsVersion
{
    public function __construct(private ?ConfigRepository $config = null)
    {
    }

    public function value(): string
    {
        $configured = $this->config?->get('app.version', null);
        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        $env = getenv('CMS_VERSION');
        if (is_string($env) && trim($env) !== '') {
            return trim($env);
        }

        return '0.12.0-dev';
    }

    public function label(): string
    {
        return 'Zoosper CMS ' . $this->value();
    }
}
