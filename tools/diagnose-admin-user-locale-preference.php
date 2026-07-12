<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale preference diagnostics\n";
print "================================================\n\n";

print 'schema_file: ' . (is_file($basePath . '/database/schema/admin_user_locale.php') ? 'yes' : 'no') . PHP_EOL;
print 'resolver_class: ' . (class_exists(\Zoosper\Core\I18n\AdminUserLocaleResolver::class) ? 'yes' : 'no') . PHP_EOL;

try {
    $pdo = null;
    $helper = $basePath . '/tools/page-content-schema-db.php';
    if (is_file($helper)) {
        require_once $helper;
        if (function_exists('zoosper_page_content_schema_pdo')) {
            $pdo = zoosper_page_content_schema_pdo($basePath);
        }
    }

    if ($pdo instanceof PDO) {
        $statement = $pdo->prepare('SHOW COLUMNS FROM `admin_users` LIKE :column');
        $statement->execute(['column' => 'locale']);
        print 'admin_users.locale: ' . ((bool) $statement->fetch(PDO::FETCH_ASSOC) ? 'yes' : 'no') . PHP_EOL;
    } else {
        print 'admin_users.locale: unknown - database helper unavailable' . PHP_EOL;
    }
} catch (Throwable $exception) {
    print 'admin_users.locale: unknown - ' . $exception->getMessage() . PHP_EOL;
}
