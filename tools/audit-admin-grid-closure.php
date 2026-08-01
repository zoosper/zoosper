<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

/** @param list<string> $needles */
$check = static function (string $path, array $needles) use ($root, &$errors): void {
    $absolute = $root . DIRECTORY_SEPARATOR . $path;
    if (!is_file($absolute)) {
        $errors[] = "Missing required file: {$path}";
        return;
    }

    $contents = (string) file_get_contents($absolute);
    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            $errors[] = "Missing signal '{$needle}' in {$path}";
        }
    }
};

$check('packages/zoosper-grid/composer.json', ['"name": "zoosper/grid"']);
$check('packages/zoosper-grid/.gitattributes', ['tests export-ignore']);
$check('packages/zoosper-admin-grid/composer.json', [
    '"name": "zoosper/admin-grid"',
    '"zoosper/grid"',
]);
$check('packages/zoosper-admin-grid/.gitattributes', ['tests export-ignore']);
$check('packages/zoosper-admin-grid/src/GridWorkspaceCapabilities.php', [
    'columnVisibility',
    'columnOrdering',
    'bookmarks',
    'csvExport',
]);
$check('packages/zoosper-admin-grid/src/GridBookmarkRepository.php', ['admin_user_id', 'grid_key']);
$check('packages/zoosper-admin-grid/src/GridViewStateResolver.php', [
    'GridColumnOrderer',
    'GridStateNormaliser',
]);
$check('packages/zoosper-admin-grid/src/GridWorkspacePageSizeOptions.php', ['20, 50, 100, 200']);
$check('packages/zoosper-admin-grid/database/migrations/202607310002_create_admin_grid_bookmarks.php', [
    'admin_grid_bookmarks',
    'admin_user_id',
    'is_default',
]);
$check('packages/zoosper-admin-grid/config/admin_assets.php', [
    'grid-workspace.js',
    'grid-compact-workspace.js',
]);
$check('packages/zoosper-admin-grid/resources/admin/js/grid-workspace.js', ['data-grid-workspace']);
$check('packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js', [
    'data-grid-page-size',
    'data-grid-remove-filter',
]);
$check('app/zoosper-page/config/controllers.php', ['Grid']);

if ($errors !== []) {
    fwrite(STDERR, "ADMIN_GRID_CLOSURE_ERRORS " . count($errors) . PHP_EOL);
    foreach ($errors as $error) {
        fwrite(STDERR, "- {$error}" . PHP_EOL);
    }
    exit(1);
}

echo "ADMIN_GRID_CLOSURE_ERRORS 0" . PHP_EOL;
echo "Result: OK" . PHP_EOL;
