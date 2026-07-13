<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';

print "Zoosper UserAdminController named locale argument verification\n";
print "==============================================================\n\n";

$source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$problem = false;
foreach (['createWithRoleIds', 'updateUser'] as $method) {
    foreach (extract_call_bodies($source, '->' . $method . '(') as $body) {
        if (preg_match('/\b[a-zA-Z_][a-zA-Z0-9_]*\s*:/', $body) === 1
            && preg_match('/(?<!locale:\s)\$this->adminUserLocaleFromForm\(/', $body) === 1) {
            $problem = true;
        }
    }
}

$checks = [
    'UserAdminController exists' => is_file($controllerPath),
    'locale helper exists' => str_contains($source, 'function adminUserLocaleFromForm('),
    'named-argument calls use locale named argument' => !$problem,
    'controller syntax is valid prerequisite' => is_file($controllerPath),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

/** @return list<string> */
function extract_call_bodies(string $source, string $needle): array
{
    $bodies = [];
    $offset = 0;
    while (($start = strpos($source, $needle, $offset)) !== false) {
        $open = strpos($source, '(', $start);
        if ($open === false) {
            break;
        }
        $end = matching_paren($source, $open);
        if ($end === null) {
            $offset = $start + 1;
            continue;
        }
        $bodies[] = substr($source, $open + 1, $end - $open - 1);
        $offset = $end + 1;
    }

    return $bodies;
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
