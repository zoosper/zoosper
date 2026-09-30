<?php
declare(strict_types=1);
use Zoosper\GithubIssues\Admin\GithubIssueAdminController;
use Zoosper\GithubIssues\GithubIssueIntegrationGate;
if (!GithubIssueIntegrationGate::enabled()) return [];
return [[
    'method' => 'GET', 'path' => '/admin/github-issues',
    'controller' => GithubIssueAdminController::class, 'action' => 'index',
    'permission' => 'github_issue.view',
]];
