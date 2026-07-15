<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/autoload.php';

use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Page\Model\Page;
use Zoosper\Site\Model\Site;

$hasher = new PasswordHasher();
$hash = $hasher->hash('secret');

if (!$hasher->verify('secret', $hash)) {
    fwrite(STDERR, "Password hashing failed.\n");
    exit(1);
}

if (Permission::AdminAccess->value !== 'admin.access') {
    fwrite(STDERR, "Permission enum failed.\n");
    exit(1);
}

$html = (new PageRenderer())->render(
    new Page(
        id: 1,
        siteId: 1,
        title: 'Test Page',
        slug: 'test-page',
        content: '<script>alert(1)</script>',
        status: 'published',
        metaTitle: null,
        metaDescription: null,
        publishedAt: gmdate('Y-m-d H:i:s'),
    ),
    new Site(
        id: 1,
        code: 'main',
        name: 'Main Website',
        status: 'active',
        homepageSlug: 'home',
    ),
);

if (str_contains($html, '<script>alert(1)</script>')) {
    fwrite(STDERR, "Page renderer did not escape content.\n");
    exit(1);
}

echo "Zoosper phase 0.3 smoke tests passed.\n";
