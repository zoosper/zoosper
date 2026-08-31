<?php

declare(strict_types=1);

namespace Zoosper\Mail\Diagnostics;

use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\Mail\Config\SmtpConfigFactory;

/** Creates redacted Mail diagnostics for an explicitly supplied scope. */
final readonly class MailConfigurationInspectorFactory
{
    public function __construct(private SmtpConfigFactory $smtp)
    {
    }

    public function forScope(ScopeContext $context): MailConfigurationInspector
    {
        return new MailConfigurationInspector($this->smtp->forScope($context));
    }

    public function forDefaultScope(): MailConfigurationInspector
    {
        return new MailConfigurationInspector($this->smtp->forDefaultScope());
    }
}










