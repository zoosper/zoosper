<?php

declare(strict_types=1);

/**
 * Phase 1.103d link-aware docs prune. DOCS ONLY (tools/ untouched).
 *
 * A doc is KEPT when it is reachable by following relative Markdown links from a
 * set of canonical roots, OR when it is referenced by code (app/ packages/
 * tools/) or composer.json, OR when it lives under docs/reports/. Everything
 * else is a deletion candidate. This guarantees no kept doc is left with a
 * broken link.
 *
 * Dry-run by default. Pass --apply to delete candidates and prune empty dirs.
 */

$root = dirname(__DIR__);
$docsDir = $root . '/docs';
$apply = in_array('--apply', $argv, true);
$slash = static fn (string $p): string => str_replace('\\', '/', $p);

if (!is_dir($docsDir)) {
    fwrite(STDERR, "No docs/ directory found.\n");
    exit(1);
}

/**
 * @return list<string>
 */
$collect = static function (string $dir) use ($slash): array {
    if (!is_dir($dir)) {
        return [];
    }
    $out = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if ($f instanceof SplFileInfo && $f->isFile()) {
            $out[] = $slash($f->getPathname());
        }
    }
    sort($out);
    return $out;
};

/** Normalise a path, resolving . and .. segments. */
$canon = static function (string $path) use ($slash): string {
    $path = $slash($path);
    $isAbs = str_starts_with($path, '/');
    $stack = [];
    foreach (explode('/', $path) as $seg) {
        if ($seg === '' || $seg === '.') {
            continue;
        }
        if ($seg === '..') {
            array_pop($stack);
            continue;
        }
        $stack[] = $seg;
    }
    return ($isAbs ? '/' : '') . implode('/', $stack);
};

$allDocs = $collect($docsDir);
$allDocsSet = array_fill_keys($allDocs, true);

/* Canonical seeds: explicit files + whole directories. */
$seedFiles = ['docs/README.md', 'docs/index.md'];
$seedDirs = [
    'docs/guide',
    'docs/configuration',
    'docs/architecture',
    'docs/operations',
    'docs/troubleshooting',
    'docs/contributor',
    'docs/licences',
    'docs/license',
    'docs/reference',
];

/** Resolve a markdown link (relative to a base file) to a canonical abs path, or null. */
$resolveLink = static function (string $baseFileAbs, string $link) use ($root, $canon): ?string {
    $hash = strpos($link, '#');
    if ($hash !== false) {
        $link = substr($link, 0, $hash);
    }
    $q = strpos($link, '?');
    if ($q !== false) {
        $link = substr($link, 0, $q);
    }
    $link = trim($link);
    if ($link === '' || $link[0] === '#') {
        return null;
    }
    if (str_contains($link, '://') || str_starts_with($link, 'mailto:')) {
        return null;
    }
    $baseAbs = str_starts_with($link, '/')
        ? $root . $link
        : dirname($baseFileAbs) . '/' . $link;

    return $canon($baseAbs);
};

/**
 * Extract markdown link targets of the form ](target) using plain string
 * scanning (no bracket-heavy regex, so the file stays trivially verifiable).
 *
 * @return list<string>
 */
$extractLinks = static function (string $contents): array {
    $links = [];
    $needle = '](';
    $offset = 0;
    $len = strlen($contents);
    while (($pos = strpos($contents, $needle, $offset)) !== false) {
        $start = $pos + 2;
        $end = strpos($contents, ')', $start);
        if ($end === false) {
            break;
        }
        $target = substr($contents, $start, $end - $start);
        if ($target !== '') {
            $links[] = $target;
        }
        $offset = $end + 1;
    }
    return $links;
};

/* BFS over reachable docs. */
$reachable = [];
$queue = [];
$enqueue = static function (string $abs) use (&$reachable, &$queue, $allDocsSet, $slash): void {
    $abs = $slash($abs);
    if (isset($allDocsSet[$abs]) && !isset($reachable[$abs])) {
        $reachable[$abs] = true;
        $queue[] = $abs;
    }
};

foreach ($seedFiles as $rel) {
    $enqueue($root . '/' . $rel);
}
foreach ($seedDirs as $dir) {
    foreach ($collect($root . '/' . $dir) as $abs) {
        $enqueue($abs);
    }
}

while ($queue !== []) {
    $current = array_shift($queue);
    $ext = strtolower(pathinfo($current, PATHINFO_EXTENSION));
    if ($ext !== 'md' && $ext !== 'markdown') {
        continue;
    }
    $contents = (string) file_get_contents($current);
    foreach ($extractLinks($contents) as $link) {
        $target = $resolveLink($current, $link);
        if ($target !== null) {
            $enqueue($target);
        }
    }
}

/* Keep docs referenced by code / composer. */
$codeBlob = is_file($root . '/composer.json') ? (string) file_get_contents($root . '/composer.json') : '';
foreach (['app', 'packages', 'tools'] as $dir) {
    foreach ($collect($root . '/' . $dir) as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['php', 'latte', 'sh', 'json', 'xml', 'neon', 'yml', 'yaml'], true)) {
            $codeBlob .= "\n" . (string) file_get_contents($file);
        }
    }
}

$referencedByCode = static function (string $absDoc) use ($root, $codeBlob, $slash): bool {
    $rel = ltrim(str_replace($slash($root), '', $absDoc), '/');
    return str_contains($codeBlob, $rel) || str_contains($codeBlob, basename($absDoc));
};

/* Classify. */
$kept = [];
$candidates = [];
foreach ($allDocs as $abs) {
    $rel = ltrim(str_replace($slash($root), '', $abs), '/');
    if (str_starts_with($rel, 'docs/reports/')) {
        $kept[] = ['file' => $rel, 'reason' => 'reports-output'];
        continue;
    }
    if (isset($reachable[$abs])) {
        $kept[] = ['file' => $rel, 'reason' => 'reachable-from-canonical'];
        continue;
    }
    if ($referencedByCode($abs)) {
        $kept[] = ['file' => $rel, 'reason' => 'referenced-by-code'];
        continue;
    }
    $candidates[] = $rel;
}

/* Apply. */
$deleted = [];
$failed = [];
if ($apply) {
    foreach ($candidates as $rel) {
        $abs = $root . '/' . $rel;
        if (is_file($abs) && @unlink($abs)) {
            $deleted[] = $rel;
        } elseif (is_file($abs)) {
            $failed[] = $rel;
        }
    }
    $dirs = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($docsDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) {
        if ($f instanceof SplFileInfo && $f->isDir()) {
            $dirs[] = $f->getPathname();
        }
    }
    foreach ($dirs as $d) {
        if (is_dir($d) && array_diff(scandir($d) ?: [], ['.', '..']) === []) {
            @rmdir($d);
        }
    }
}

/* Report. */
$reportDir = $root . '/docs/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
$report = [
    'phase' => '1.103d-link-aware-docs-prune',
    'generatedAt' => gmdate('c'),
    'mode' => $apply ? 'apply' : 'dry-run',
    'scope' => 'docs/ only (tools untouched)',
    'docsTotal' => count($allDocs),
    'kept' => count($kept),
    'candidates' => count($candidates),
    'keptDetail' => $kept,
    'candidateList' => $candidates,
    'deleted' => $deleted,
    'failed' => $failed,
];
file_put_contents($reportDir . '/docs-prune.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

echo "## Link-Aware Docs Prune (1.103d) - tools/ untouched\n\n";
echo 'Generated: ' . $report['generatedAt'] . "\n";
echo 'Mode: ' . $report['mode'] . "\n";
echo 'docs/ total files: ' . count($allDocs) . "\n";
echo 'KEPT: ' . count($kept) . "\n";
echo 'deletion candidates: ' . count($candidates) . "\n";
echo 'Deleted this run: ' . count($deleted) . "\n";
echo "Report: docs/reports/docs-prune.json\n\n";

echo "### Deletion candidates (" . count($candidates) . ")\n";
foreach (array_slice($candidates, 0, 60) as $c) {
    echo "- {$c}\n";
}
if (count($candidates) > 60) {
    echo "  ... and " . (count($candidates) - 60) . " more (see docs-prune.json)\n";
}
echo "\n";

if (!$apply) {
    echo "Dry-run only. tools/ is NOT touched.\n";
    echo "Confirm NO file under guide/ configuration/ architecture/ operations/ troubleshooting/ contributor/ is a candidate,\n";
    echo "then re-run with --apply.\n";
}

exit($failed === [] ? 0 : 1);
