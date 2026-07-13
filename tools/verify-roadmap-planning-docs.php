<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$roadmap = read($basePath . '/docs/roadmap/carry-forward-roadmap.md');
$plan = read($basePath . '/docs/planning/phase-1.18-extension-data-persistence-plan.md');
$summary = read($basePath . '/docs/architecture/modular-entity-save-pipeline-summary.md');

print "Zoosper roadmap and extension-data planning verification\n";
print "=======================================================\n\n";

$checks = [
    'roadmap restores pagination TODO' => str_contains($roadmap, 'Add pagination to admin grids'),
    'roadmap restores customer account TODO' => str_contains($roadmap, 'customer login and customer account management'),
    'roadmap restores mail log menu TODO' => str_contains($roadmap, 'admin menu link to mail logs'),
    'roadmap restores form key / CSRF TODO' => str_contains($roadmap, 'form key / CSRF'),
    'roadmap includes SQL placeholder guidance' => str_contains($roadmap, 'Every SQL placeholder token must have a matching execute/bind parameter'),
    'roadmap includes admin notice styling guidance' => str_contains($roadmap, 'Admin notices must retain visible success/error/warning styling'),
    'roadmap includes extension data phase' => str_contains($roadmap, 'Entity Extension Data Persistence Table'),
    'planning document includes pros' => str_contains($plan, '## Pros'),
    'planning document includes cons' => str_contains($plan, '## Cons'),
    'planning document includes entity_extension_values table' => str_contains($plan, 'entity_extension_values'),
    'terminology doc explains data object' => str_contains($summary, 'Entity data object'),
    'terminology doc explains extension data' => str_contains($summary, 'Extension attributes / extension data'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function read(string $path): string
{
    return is_file($path) ? (string) file_get_contents($path) : '';
}
