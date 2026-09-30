<?php
declare(strict_types=1);
use Zoosper\GithubIssues\GithubIssueIntegrationGate;
if (!GithubIssueIntegrationGate::enabled()) return [];
return [[
    'code' => 'github-issues', 'label' => 'GitHub Issues', 'url' => '/admin/github-issues',
    'permission' => 'github_issue.view', 'sort_order' => 40, 'group' => 'Content', 'icon' => 'issues',
]];
