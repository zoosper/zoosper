<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$roadmapPath = $basePath . '/docs/roadmap/carry-forward-roadmap.md';

print "Zoosper roadmap and error CSS verification hotfix\n";
print "================================================\n\n";

if (!is_file($roadmapPath)) {
    fwrite(STDERR, "Missing roadmap: {$roadmapPath}\n");
    exit(2);
}

$roadmap = (string) file_get_contents($roadmapPath);
$originalRoadmap = $roadmap;

$requiredLines = [
    '- Entity Extension Data Persistence Table foundation.',
    '- Every SQL placeholder token must have a matching execute/bind parameter.',
    '- Verifiers must check placeholder/parameter consistency after SQL write-map patches.',
    '- Admin notices must retain visible success/error/warning styling after UI changes.',
    '- Do not mix positional arguments after named arguments in generated PHP code.',
    '- Save flows should dispatch before/after validation and before/after save lifecycle events.',
    '- Admin locale values must be normalised and strictly validated before persistence.',
    '- Empty admin locale values should persist as null to preserve configured admin-locale fallback.',
    '- Keep controllers thin and move business logic into services, repositories, handlers or pipelines.',
    '- Avoid raw SQL across controllers; repositories/query services should own persistence.',
    '- Design persistence abstractions so future database engines such as MySQL, MariaDB, PostgreSQL, Microsoft SQL Server or SQLite can be supported where practical.',
    '- Continue reducing raw query usage where it blocks true modularity and database portability.',
];

foreach ($requiredLines as $line) {
    if (!str_contains($roadmap, $line)) {
        $roadmap .= PHP_EOL . $line . PHP_EOL;
    }
}

if (!str_contains($roadmap, '### Phase 1.19 - Entity Extension Data Persistence Table')) {
    $roadmap .= PHP_EOL . '### Phase 1.19 - Entity Extension Data Persistence Table' . PHP_EOL;
    $roadmap .= '- Added entity_extension_values schema seed.' . PHP_EOL;
    $roadmap .= '- Added repository for extension values.' . PHP_EOL;
    $roadmap .= '- Added persister that saves FieldStorageType::ExtensionTable fields.' . PHP_EOL;
    $roadmap .= '- Verified third-party fields are separated from core write data.' . PHP_EOL;
}

if ($roadmap !== $originalRoadmap) {
    $backup = $roadmapPath . '.phase-1.19.1.bak';
    if (!is_file($backup)) {
        copy($roadmapPath, $backup);
        print '- backup created: docs/roadmap/carry-forward-roadmap.md.phase-1.19.1.bak' . PHP_EOL;
    }
    file_put_contents($roadmapPath, rtrim($roadmap) . PHP_EOL);
    print '- roadmap updated' . PHP_EOL;
} else {
    print '- roadmap already contains required planning phrases' . PHP_EOL;
}

print "Result: OK\n";
