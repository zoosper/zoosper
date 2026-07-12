<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$reportPath = $basePath . '/var/reports/user-admin-rendering-pattern.txt';

print "Zoosper UserAdminController rendering pattern inspection\n";
print "========================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

$source = (string) file_get_contents($controllerPath);
$lines = preg_split('/\R/', $source) ?: [];
$signals = [
    'uses_heredoc' => preg_match('/<<<[A-Z_]+/', $source) === 1,
    'uses_nowdoc' => preg_match('/<<<\'[A-Z_]+\'/', $source) === 1,
    'contains_return_new_response' => str_contains($source, 'new Response('),
    'contains_admin_view_renderer' => str_contains($source, 'AdminViewRenderer'),
    'contains_form_tag' => str_contains($source, '<form'),
    'contains_email_field' => str_contains($source, 'name="email"') || str_contains($source, "name='email'"),
    'contains_locale_field' => str_contains($source, 'name="locale"') || str_contains($source, "name='locale'"),
    'contains_raw_template_tag_after_opening_php' => contains_embedded_template_tag($source),
];

$formLines = matching_lines($lines, ['<form', 'name="email"', "name='email'", 'name="password"', 'name="status"', 'name="locale"', "name='locale'"]);

$report = [];
$report[] = 'Zoosper UserAdminController rendering pattern report';
$report[] = '===================================================';
$report[] = 'Controller: app/zoosper-admin/src/Controller/UserAdminController.php';
$report[] = '';
$report[] = 'Detected signals:';
foreach ($signals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}
$report[] = '';
$report[] = 'Relevant form lines:';
foreach ($formLines as [$number, $text]) {
    $report[] = str_pad((string) $number, 5, ' ', STR_PAD_LEFT) . ': ' . $text;
}
$report[] = '';
$report[] = 'Recommended safe integration approach:';
$report[] = '- Do not insert raw <?= ... ?> tags into UserAdminController.';
$report[] = '- Build locale field HTML in PHP variables before the heredoc/string-rendered form.';
$report[] = '- Escape selected values and labels before interpolation.';
$report[] = '- Insert only a variable placeholder such as {$localeFieldHtml} into the form output.';
$report[] = '- Keep LoginController untouched.';

if (!is_dir(dirname($reportPath))) {
    mkdir(dirname($reportPath), 0775, true);
}
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

foreach ($report as $line) {
    print $line . PHP_EOL;
}

print "\nReport: {$reportPath}\n";
print "Result: OK\n";

function matching_lines(array $lines, array $needles): array
{
    $matches = [];
    foreach ($lines as $index => $line) {
        foreach ($needles as $needle) {
            if (str_contains($line, $needle)) {
                $matches[] = [$index + 1, trim($line)];
                break;
            }
        }
    }

    return $matches;
}

function contains_embedded_template_tag(string $source): bool
{
    $withoutOpeningTag = preg_replace('/^\s*<\?php\s*/', '', $source, 1) ?? $source;

    return str_contains($withoutOpeningTag, '<?=') || str_contains($withoutOpeningTag, '<?php');
}
