<?php
declare(strict_types=1);
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\GithubIssues\Admin\GithubIssueAdminController;
use Zoosper\GithubIssues\GithubIssueDataSourceFactory;
use Zoosper\GithubIssues\GithubIssueIntegrationGate;
if (!GithubIssueIntegrationGate::enabled()) return [];
return [GithubIssueAdminController::class => static fn (ServiceContainer $services): GithubIssueAdminController => new GithubIssueAdminController(
    guard: $services->get(SessionGuard::class),
    layout: $services->get(AdminLayoutRendererInterface::class),
    dataSources: new GithubIssueDataSourceFactory(),
    config: $services->get(ConfigRepository::class)->array('github_issues'),
    adminUrls: $services->get(AdminUrlGenerator::class),
)];
