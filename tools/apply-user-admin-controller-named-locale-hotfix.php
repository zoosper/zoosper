<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';

print "Zoosper UserAdminController named locale argument hotfix\n";
print "=======================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

$source = (string) file_get_contents($controllerPath);
$original = $source;
$source = fix_locale_argument_in_calls($source, ['createWithRoleIds', 'updateUser']);

if ($source === $original) {
    print "- no positional locale argument after named arguments found\n";
    print "Result: OK\n";
    exit(0);
}

$backup = $controllerPath . '.phase-1.17.1.bak';
if (!is_file($backup)) {
    copy($controllerPath, $backup);
    print '- backup created: app/zoosper-admin/src/Controller/UserAdminController.php.phase-1.17.1.bak' . PHP_EOL;
}

file_put_contents($controllerPath, $source);
print '- updated app/zoosper-admin/src/Controller/UserAdminController.php' . PHP_EOL;
print "Result: OK\n";

/** @param list<string> $methods */
function fix_locale_argument_in_calls(string $source, array $methods): string
{
    foreach ($methods as $method) {
        $offset = 0;
        while (($start = strpos($source, '->' . $method . '(', $offset)) !== false) {
            $open = strpos($source, '(', $start);
            if ($open === false) {
                break;
            }

            $end = matching_paren($source, $open);
            if ($end === null) {
                $offset = $start + 1;
                continue;
            }

            $body = substr($source, $open + 1, $end - $open - 1);
            if (!str_contains($body, 'adminUserLocaleFromForm(')) {
                $offset = $end + 1;
                continue;
            }

            if (preg_match('/\b[a-zA-Z_][a-zA-Z0-9_]*\s*:/', $body) !== 1) {
                $offset = $end + 1;
                continue;
            }

            $newBody = preg_replace(
                '/(?<![A-Za-z0-9_])\$this->adminUserLocaleFromForm\(/',
                'locale: $this->adminUserLocaleFromForm(',
                $body,
                1
            ) ?? $body;

            if ($newBody !== $body) {
                $source = substr($source, 0, $open + 1) . $newBody . substr($source, $end);
                $offset = $open + 1 + strlen($newBody);
                continue;
            }

            $offset = $end + 1;
        }
    }

    return $source;
}

function matching_paren(string $source, int $open): ?int
{
    $depth = 0;
    $length = strlen($source);
    $quote = null;
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
        if ($char === '(') {
            $depth++;
        } elseif ($char === ')') {
            $depth--;
            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}
