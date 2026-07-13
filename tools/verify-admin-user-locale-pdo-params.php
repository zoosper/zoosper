<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper AdminUser locale PDO parameter verification\n";
print "==================================================\n\n";

$repository = $repositoryPath !== null ? (string) file_get_contents($repositoryPath) : '';
$problems = [];
foreach (extract_execute_blocks($repository) as $index => $block) {
    if (str_contains($block['statement_context'], ':locale') && !str_contains($block['execute_array'], "'locale' =>") && !str_contains($block['execute_array'], '"locale" =>')) {
        $problems[] = 'execute block ' . ($index + 1) . ' has :locale SQL but no locale execute parameter';
    }
}

$checks = [
    'AdminUserRepository exists' => $repositoryPath !== null,
    'repository has locale SQL token' => str_contains($repository, ':locale'),
    'every locale SQL execute block binds locale' => $problems === [],
    'repository create SQL writes locale' => str_contains($repository, 'INSERT INTO admin_users (email, name, password_hash, status, locale,'),
    'repository update SQL writes locale' => str_contains($repository, 'status = :status, locale = :locale'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

foreach ($problems as $problem) {
    print '- problem: ' . $problem . PHP_EOL;
}

print "\nRepository: " . ($repositoryPath !== null ? relative_path($basePath, $repositoryPath) : 'not found') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function extract_execute_blocks(string $source): array
{
    $blocks = [];
    $offset = 0;
    while (($executePos = strpos($source, '->execute([', $offset)) !== false) {
        $arrayOpen = strpos($source, '[', $executePos);
        $arrayClose = $arrayOpen === false ? null : matching_square_bracket($source, $arrayOpen);
        if ($arrayOpen === false || $arrayClose === null) {
            $offset = $executePos + 1;
            continue;
        }
        $statementStart = strrpos(substr($source, 0, $executePos), '$statement');
        $statementStart = $statementStart === false ? max(0, $executePos - 1200) : $statementStart;
        $blocks[] = [
            'statement_context' => substr($source, $statementStart, $executePos - $statementStart),
            'execute_array' => substr($source, $arrayOpen + 1, $arrayClose - $arrayOpen - 1),
        ];
        $offset = $arrayClose + 1;
    }

    return $blocks;
}

function matching_square_bracket(string $source, int $open): ?int
{
    $depth = 0;
    $quote = null;
    $length = strlen($source);
    for ($i = $open; $i < $length; $i++) {
        $char = $source[$i];
        if ($quote !== null) {
            if ($char === '\\') {
                $i++;
                continue;
            }
            if ($char === $quote) {
                $quote = null;
            }
            continue;
        }
        if ($char === '\'' || $char === '"') {
            $quote = $char;
            continue;
        }
        if ($char === '[') {
            $depth++;
        } elseif ($char === ']') {
            $depth--;
            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}

function find_file_containing(string $basePath, string $needle): ?string
{
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $path = $file->getPathname();
                if (str_contains((string) file_get_contents($path), $needle)) {
                    return $path;
                }
            }
        }
    }

    return null;
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
