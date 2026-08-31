<?php

declare(strict_types=1);

namespace Zoosper\Editor\Config;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;

/** Creates immutable content-editor runtime configuration for an explicit scope. */
final readonly class ContentEditorRuntimeConfigFactory
{
    public function __construct(
        private ConfigRepository $project,
        private ScopeConfigRepository $scoped,
    ) {
    }

    public function forScope(ScopeContext $scope): ContentEditorRuntimeConfig
    {
        return new ContentEditorRuntimeConfig(
            $this->project,
            $this->scoped,
            $scope,
        );
    }

    public function forDefaultScope(): ContentEditorRuntimeConfig
    {
        return $this->forScope(ScopeContext::default());
    }
}
