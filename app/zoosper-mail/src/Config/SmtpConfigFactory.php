<?php

declare(strict_types=1);

namespace Zoosper\Mail\Config;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Config\Scope\ScopeContext;

/**
 * Creates immutable SMTP runtime configuration for an explicit scope.
 *
 * The default service remains suitable for system mail. Request-aware callers
 * may use this factory when a concrete site/store/website context is available.
 */
final readonly class SmtpConfigFactory
{
    public function __construct(
        private ConfigRepository $project,
        private ScopeConfigRepository $scoped,
    ) {
    }

    public function forScope(ScopeContext $context): SmtpConfig
    {
        return new SmtpConfig($this->project, $this->scoped, $context);
    }

    public function forDefaultScope(): SmtpConfig
    {
        return $this->forScope(ScopeContext::default());
    }
}
