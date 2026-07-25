<?php

declare(strict_types=1);

/**
 * Phase 1.87 page momentum schema probe.
 *
 * Read-only. Inspects the `pages` table and reports which columns exist, then
 * suggests a ready-to-paste column map for the schema-adaptive query. Supports
 * SQLite (file path or :memory:) and any PDO DSN.
 *
 * Usage:
 *   php8.5 tools/probe-page-momentum-schema.php --database=/path/to/app.sqlite
 *   php8.5 tools/probe-page-momentum-schema.php --database="mysql:host=localhost;dbname=zoosper" --user=root --password=secret
 *   php8.5 tools/probe-page-momentum-schema.php --database=... --table=cms_pages
 */

$root = dirname(__DIR__);

$options = getopt('', ['database:', 'user::', 'password::', 'table::']);
$database = $options['database'] ?? null;
$user = $options['user'] ?? null;
$password = $options['password'] ?? null;
$table = $options['table'] ?? 'pages';

if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $table) !== 1) {
    fwrite(STDERR, "Invalid --table value.\n");
    exit(1);
}

if ($database === null) {
    fwrite(STDERR, "Missing required --database=<sqlite path or PDO DSN>.\n");
    exit(1);
}

// Turn a bare file path into a sqlite DSN for convenience.
$dsn = str_contains((string) $database, ':') ? (string) $database : 'sqlite:' . $database;

try {
    $pdo = new PDO($dsn, $user !== null ? (string) $user : null, $password !== null ? (string) $password : null);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    fwrite(STDERR, 'Could not connect: ' . $e->getMessage() . "\n");
    exit(1);
}

$columns = [];
try {
    $stmt = $pdo->query('SELECT * FROM ' . $table . ' LIMIT 0');
    if ($stmt !== false) {
        for ($i = 0, $n = $stmt->columnCount(); $i < $n; $i++) {
            $meta = $stmt->getColumnMeta($i);
            if (is_array($meta) && isset($meta['name'])) {
                $columns[] = (string) $meta['name'];
            }
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Could not read table "' . $table . '": ' . $e->getMessage() . "\n");
    exit(1);
}

/**
 * Guess the best matching column from a list of candidate names.
 */
$guess = static function (array $candidates) use ($columns): ?string {
    foreach ($candidates as $candidate) {
        foreach ($columns as $column) {
            if (strcasecmp($column, $candidate) === 0) {
                return $column;
            }
        }
    }
    return null;
};

$suggested = [
    'table' => $table,
    'status' => $guess(['status', 'state', 'publish_status', 'is_published']),
    'title' => $guess(['title', 'name', 'heading', 'label']),
    'published_at' => $guess(['published_at', 'publish_date', 'published_on', 'live_at']),
    'updated_at' => $guess(['updated_at', 'modified_at', 'changed_at', 'updated_on']),
];

$docsReportDir = $root . '/docs/reports';
if (!is_dir($docsReportDir) && !mkdir($docsReportDir, 0775, true) && !is_dir($docsReportDir)) {
    fwrite(STDERR, "Unable to create reports directory.\n");
    exit(1);
}

$report = [
    'phase' => '1.87-page-momentum-schema-probe',
    'generatedAt' => gmdate('c'),
    'table' => $table,
    'columnsFound' => $columns,
    'suggestedMap' => $suggested,
];
file_put_contents(
    $docsReportDir . '/page-momentum-schema-probe.json',
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

echo "## Page Momentum Schema Probe\n\n";
echo 'Generated: ' . $report['generatedAt'] . "\n";
echo 'Table: ' . $table . "\n";
echo 'Columns found: ' . (count($columns) > 0 ? implode(', ', $columns) : '(none)') . "\n\n";

echo "### Suggested column map (paste into your DI binding)\n\n";
echo "PageMomentumColumnMap::fromArray([\n";
foreach ($suggested as $key => $value) {
    if ($key === 'table') {
        echo "    'table' => '" . $value . "',\n";
        continue;
    }
    if ($value === null) {
        echo "    // '" . $key . "' => '???',  // NOT FOUND - set this manually\n";
    } else {
        echo "    '" . $key . "' => '" . $value . "',\n";
    }
}
echo "]);\n\n";

$missing = array_filter(
    $suggested,
    static fn ($v, $k) => $k !== 'table' && $v === null,
    ARRAY_FILTER_USE_BOTH
);
if ($missing !== []) {
    echo "Note: some logical fields were not auto-detected (" . implode(', ', array_keys($missing)) . ").\n";
    echo "The dashboard will still work; unmatched cards report 0/null until you set them.\n";
}

echo "\nReport: docs/reports/page-momentum-schema-probe.json\n";
