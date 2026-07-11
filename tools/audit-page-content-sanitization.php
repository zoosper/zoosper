<?php

declare(strict_types=1);

use Zoosper\Page\Sanitization\PageContentSanitizationResult;

/** @var \Zoosper\Page\Sanitization\PageContentSanitizationAuditor $auditor */
$auditor = require __DIR__ . '/bootstrap-page-content-sanitization-auditor.php';
$options = getopt('', ['pages', 'revisions', 'show-samples', 'sample-limit::']);
$scanPages = isset($options['pages']) || !isset($options['revisions']);
$scanRevisions = isset($options['revisions']);
$sampleLimit = max(0, (int) ($options['sample-limit'] ?? 10));
$showSamples = isset($options['show-samples']);

print "Zoosper page content sanitisation audit\n";
print "=======================================\n\n";

$results = [];
if ($scanPages) {
    $results[] = $auditor->auditPages($sampleLimit);
}
if ($scanRevisions) {
    $results[] = $auditor->auditRevisions($sampleLimit);
}

$needsReview = false;
foreach ($results as $result) {
    printResult($result, $showSamples);
    $needsReview = $needsReview || $result->changed > 0;
}

print "Result: " . ($needsReview ? 'REVIEW_REQUIRED' : 'OK') . PHP_EOL;
exit($needsReview ? 1 : 0);

function printResult(PageContentSanitizationResult $result, bool $showSamples): void
{
    print $result->table . PHP_EOL;
    if (!$result->tableExists) {
        print "- table: missing\n\n";
        return;
    }

    print '- scanned: ' . $result->scanned . PHP_EOL;
    print '- would_change: ' . $result->changed . PHP_EOL;

    if ($showSamples && $result->changedRows() !== []) {
        print "- samples:\n";
        foreach ($result->changedRows() as $row) {
            print '  - id=' . $row['id']
                . ' before_length=' . $row['before_length']
                . ' after_length=' . $row['after_length']
                . ' patterns=' . implode(',', $row['patterns'])
                . PHP_EOL;
        }
    }

    print PHP_EOL;
}
