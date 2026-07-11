<?php

declare(strict_types=1);

use Zoosper\Page\Sanitization\PageContentSanitizationResult;

/** @var \Zoosper\Page\Sanitization\PageContentSanitizationAuditor $auditor */
$auditor = require __DIR__ . '/bootstrap-page-content-sanitization-auditor.php';
$options = getopt('', ['pages', 'revisions', 'yes', 'sample-limit::']);

if (!isset($options['yes'])) {
    fwrite(STDERR, "Refusing to repair without --yes. Backup your database first.\n");
    exit(2);
}

if (!isset($options['pages']) && !isset($options['revisions'])) {
    fwrite(STDERR, "Choose at least one target: --pages and/or --revisions.\n");
    exit(2);
}

$sampleLimit = max(0, (int) ($options['sample-limit'] ?? 10));

print "Zoosper page content sanitisation repair\n";
print "========================================\n\n";
print "Safety reminder: backup the database before repairing existing content.\n\n";

$results = [];
if (isset($options['pages'])) {
    $results[] = $auditor->repairPages($sampleLimit);
}
if (isset($options['revisions'])) {
    $results[] = $auditor->repairRevisions($sampleLimit);
}

foreach ($results as $result) {
    printRepairResult($result);
}

print "Result: OK\n";

function printRepairResult(PageContentSanitizationResult $result): void
{
    print $result->table . PHP_EOL;
    if (!$result->tableExists) {
        print "- table: missing\n\n";
        return;
    }

    print '- scanned: ' . $result->scanned . PHP_EOL;
    print '- changed: ' . $result->changed . PHP_EOL;
    print '- repaired: ' . $result->repaired . PHP_EOL;

    if ($result->changedRows() !== []) {
        print "- sample changed IDs: " . implode(', ', array_map(static fn (array $row): int => (int) $row['id'], $result->changedRows())) . PHP_EOL;
    }

    print PHP_EOL;
}
