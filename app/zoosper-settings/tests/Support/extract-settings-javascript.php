<?php

declare(strict_types=1);

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php extract-settings-javascript.php VIEW OUTPUT\n");
    exit(2);
}

$view = file_get_contents($argv[1]);
if ($view === false) {
    fwrite(STDERR, "Unable to read Settings view.\n");
    exit(2);
}

preg_match_all('/<script([^>]*)>(.*?)<\/script>/si', $view, $matches, PREG_SET_ORDER);
$blocks = [];
foreach ($matches as $match) {
    if (str_contains(strtolower($match[1]), 'application/json')) {
        continue;
    }
    if (str_contains($match[2], '<?')) {
        fwrite(STDERR, "Executable JavaScript contains unresolved PHP.\n");
        exit(1);
    }
    $blocks[] = $match[2];
}
if ($blocks === []) {
    fwrite(STDERR, "No executable JavaScript blocks found.\n");
    exit(1);
}
file_put_contents($argv[2], implode("\n", $blocks));
echo 'Extracted ' . count($blocks) . " executable JavaScript block(s).\n";










