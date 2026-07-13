<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper AdminUser locale PDO parameter hotfix\n";
print "============================================\n\n";

if ($repositoryPath === null || !is_file($repositoryPath)) {
    fwrite(STDERR, "Missing AdminUserRepository.\n");
    exit(2);
}

$source = (string) file_get_contents($repositoryPath);
$original = $source;
$source = ensure_locale_param_for_each_locale_sql($source);

if ($source === $original) {
    print '- repository locale parameters already appear complete\n';
    print "Result: OK\n";
    exit(0);
}

$backup = $repositoryPath . '.phase-1.17.2.bak';
if (!is_file($backup)) {
    copy($repositoryPath, $backup);
    print '- backup created: ' . relative_path($basePath, $backup) . PHP_EOL;
}

file_put_contents($repositoryPath, $source);
print '- updated ' . relative_path($basePath, $repositoryPath) . PHP_EOL;
print "Result: OK\n";

function ensure_locale_param_for_each_locale_sql(string $source): string
{
    $offset = 0;
    while (($executePos = strpos($source, '->execute([', $offset)) !== false) {
        $arrayOpen = strpos($source, '[', $executePos);
        if ($arrayOpen === false) {
            break;
        }

        $arrayClose = matching_square_bracket($source, $arrayOpen);
        if ($arrayClose === null) {
            $offset = $executePos + 1;
            continue;
        }

        $statementStart = find_statement_start($source, $executePos);
        $statementContext = substr($source, max(0, $statementStart - 1200), $executePos - max(0, $statementStart - 1200));
        $executeArray = substr($source, $arrayOpen + 1, $arrayClose - $arrayOpen - 1);

        if (!str_contains($statementContext, ':locale') || str_contains($executeArray, "'locale' =>") || str_contains($executeArray, '"locale" =>')) {
            $offset = $arrayClose + 1;
            continue;
        }

        $insertAt = find_insert_position_after_status($executeArray);
        if ($insertAt === null) {
            $insertAt = strlen(rtrim($executeArray));
            $indent = detect_array_item_indent($executeArray);
            $prefix = str_ends_with(trim($executeArray), ',') ? PHP_EOL : ',' . PHP_EOL;
            $addition = $prefix . $indent . "'locale' => \$locale,";
        } else {
            [$relativePosition, $indent] = $insertAt;
            $addition = ',' . PHP_EOL . $indent . "'locale' => \$locale";
            $source = substr($source, 0, $arrayOpen + 1 + $relativePosition) . $addition . substr($source, $arrayOpen + 1 + $relativePosition);
            $offset = $arrayClose + strlen($addition) + 1;
            continue;
        }

        $source = substr($source, 0, $arrayOpen + 1 + $insertAt) . $addition . substr($source, $arrayOpen + 1 + $insertAt);
        $offset = $arrayClose + strlen($addition) + 1;
    }

    return $source;
}

function find_insert_position_after_status(string $executeArray): ?array
{
    if (preg_match('/[\'\"]status[\'\"]\s*=>\s*\$status/', $executeArray, $matches, PREG_OFFSET_CAPTURE) !== 1) {
        return null;
    }

    $end = $matches[0][1] + strlen($matches[0][0]);
    $lineStart = strrpos(substr($executeArray, 0, $matches[0][1]), "\n");
    $lineStart = $lineStart === false ? 0 : $lineStart + 1;
    $line = substr($executeArray, $lineStart, $matches[0][1] - $lineStart);
    $indent = preg_match('/^(\s*)/', $line, $indentMatches) === 1 ? $indentMatches[1] : '            ';

    return [$end, $indent];
}

function detect_array_item_indent(string $executeArray): string
{
    if (preg_match('/\n(\s*)[\'\"][A-Za-z0-9_]+[\'\"]\s*=>/', $executeArray, $matches) === 1) {
        return $matches[1];
    }

    return '            ';
}

function find_statement_start(string $source, int $offset): int
{
    $position = strrpos(substr($source, 0, $offset), '$statement');
    return $position === false ? max(0, $offset - 1200) : $position;
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
