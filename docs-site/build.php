<?php

declare(strict_types=1);

$siteRoot = __DIR__;
$repoRoot = dirname(__DIR__);
$docsRoot = $repoRoot . '/docs';
$outputRoot = $siteRoot . '/build';

$navigation = [
    'Overview' => ['README'],
    'Getting started' => ['getting-started', 'deployment', 'upgrade', 'troubleshooting'],
    'User guide' => ['user-guide'],
    'Developer guide' => ['developer-guide', 'architecture', 'modules', 'configuration', 'cli', 'api', 'admin', 'themes', 'testing'],
    'Project' => ['release-checklist'],
];

$labels = [
    'README' => 'Overview', 'getting-started' => 'Getting Started', 'user-guide' => 'User Guide',
    'developer-guide' => 'Developer Guide', 'architecture' => 'Architecture', 'modules' => 'Modules',
    'configuration' => 'Configuration', 'cli' => 'Command Line', 'api' => 'API', 'admin' => 'Admin',
    'themes' => 'Themes', 'deployment' => 'Deployment', 'upgrade' => 'Upgrade',
    'troubleshooting' => 'Troubleshooting', 'testing' => 'Testing',
    'release-checklist' => 'Release Checklist',
];

function removeDirectory(string $directory): void
{
    if (!is_dir($directory)) { return; }
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($items as $item) { $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()); }
    rmdir($directory);
}

function pageUrl(string $id): string { return $id === 'README' ? '/' : '/' . $id . '/'; }
function escapeHtml(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function inlineMarkdown(string $text): string
{
    $tokens = [];
    $text = preg_replace_callback('/`([^`]+)`/', static function (array $match) use (&$tokens): string {
        $key = '@@CODE' . count($tokens) . '@@';
        $tokens[$key] = '<code>' . escapeHtml($match[1]) . '</code>';
        return $key;
    }, $text) ?? $text;
    $text = escapeHtml($text);
    $text = preg_replace_callback(
        '/\[([^\]]+)\]\(([^)]+)\)/',
        static function (array $match): string {
            $label = $match[1];
            $href = html_entity_decode($match[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (!preg_match('~^(?:https?:|mailto:|#)~', $href)) {
                $parts = parse_url($href);
                $path = is_array($parts) ? (string) ($parts['path'] ?? '') : $href;
                $fragment = is_array($parts) && isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
                if (str_ends_with($path, '.md')) {
                    $id = basename($path, '.md');
                    $href = pageUrl($id) . $fragment;
                }
            }
            return '<a href="' . escapeHtml($href) . '">' . $label . '</a>';
        },
        $text,
    ) ?? $text;
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text) ?? $text;
    $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text) ?? $text;
    return strtr($text, $tokens);
}

function markdownToHtml(string $markdown): array
{
    $lines = preg_split('/\R/', $markdown) ?: [];
    $html = [];
    $title = 'Zoosper CMS';
    $paragraph = [];
    $listType = null;
    $inCode = false;
    $codeLanguage = '';
    $code = [];

    $flushParagraph = static function () use (&$paragraph, &$html): void {
        if ($paragraph !== []) { $html[] = '<p>' . inlineMarkdown(implode(' ', $paragraph)) . '</p>'; $paragraph = []; }
    };
    $closeList = static function () use (&$listType, &$html): void {
        if ($listType !== null) { $html[] = '</' . $listType . '>'; $listType = null; }
    };

    foreach ($lines as $line) {
        if (str_starts_with($line, '```')) {
            $flushParagraph(); $closeList();
            if (!$inCode) { $inCode = true; $codeLanguage = trim(substr($line, 3)); $code = []; }
            else {
                $class = $codeLanguage === '' ? '' : ' class="language-' . escapeHtml($codeLanguage) . '"';
                $html[] = '<pre><code' . $class . '>' . escapeHtml(implode("\n", $code)) . '</code></pre>';
                $inCode = false;
            }
            continue;
        }
        if ($inCode) { $code[] = $line; continue; }
        if (trim($line) === '') { $flushParagraph(); $closeList(); continue; }
        if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $match)) {
            $flushParagraph(); $closeList();
            $level = strlen($match[1]); $heading = trim($match[2]);
            if ($level === 1) { $title = trim(strip_tags(inlineMarkdown($heading))); }
            $id = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', strip_tags($heading)) ?? '', '-'));
            $html[] = "<h{$level} id=\"" . escapeHtml($id) . "\">" . inlineMarkdown($heading) . "</h{$level}>";
            continue;
        }
        if (preg_match('/^[-*]\s+(.+)$/', $line, $match)) {
            $flushParagraph(); if ($listType !== 'ul') { $closeList(); $listType = 'ul'; $html[] = '<ul>'; }
            $html[] = '<li>' . inlineMarkdown($match[1]) . '</li>'; continue;
        }
        if (preg_match('/^\d+\.\s+(.+)$/', $line, $match)) {
            $flushParagraph(); if ($listType !== 'ol') { $closeList(); $listType = 'ol'; $html[] = '<ol>'; }
            $html[] = '<li>' . inlineMarkdown($match[1]) . '</li>'; continue;
        }
        if (str_starts_with($line, '> ')) { $flushParagraph(); $closeList(); $html[] = '<blockquote>' . inlineMarkdown(substr($line, 2)) . '</blockquote>'; continue; }
        $paragraph[] = trim($line);
    }
    $flushParagraph(); $closeList();
    if ($inCode) { $html[] = '<pre><code>' . escapeHtml(implode("\n", $code)) . '</code></pre>'; }
    return [$title, implode("\n", $html)];
}

function navigationHtml(array $navigation, array $labels, string $current): string
{
    $html = '<nav class="sidebar" aria-label="Documentation">';
    foreach ($navigation as $section => $ids) {
        $html .= '<div class="nav-section"><strong>' . escapeHtml($section) . '</strong><ul>';
        foreach ($ids as $id) {
            $active = $id === $current ? ' class="active" aria-current="page"' : '';
            $html .= '<li><a' . $active . ' href="' . pageUrl($id) . '">' . escapeHtml($labels[$id] ?? $id) . '</a></li>';
        }
        $html .= '</ul></div>';
    }
    return $html . '</nav>';
}

function layout(string $title, string $content, string $navigation): string
{
    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="description" content="Zoosper CMS documentation"><title>' . escapeHtml($title) . ' · Zoosper CMS</title>'
        . '<link rel="icon" href="/assets/favicon.svg"><link rel="stylesheet" href="/assets/site.css"></head><body>'
        . '<header><a class="brand" href="/"><img src="/assets/logo.svg" alt="">Zoosper CMS</a><span>Documentation</span>'
        . '<a href="https://github.com/dbashyal/zoosper-cms">GitHub</a></header><div class="shell">' . $navigation
        . '<main class="content">' . $content . '</main></div><footer>Zoosper CMS documentation</footer></body></html>';
}

removeDirectory($outputRoot);
mkdir($outputRoot . '/assets', 0775, true);
copy($siteRoot . '/assets/site.css', $outputRoot . '/assets/site.css');
copy($siteRoot . '/assets/logo.svg', $outputRoot . '/assets/logo.svg');
copy($siteRoot . '/assets/favicon.svg', $outputRoot . '/assets/favicon.svg');

$known = [];
foreach ($navigation as $ids) { foreach ($ids as $id) { $known[$id] = true; } }
foreach (array_keys($known) as $id) {
    $source = $docsRoot . '/' . $id . '.md';
    if (!is_file($source)) { throw new RuntimeException("Missing canonical document: {$source}"); }
    [$title, $content] = markdownToHtml((string) file_get_contents($source));
    $directory = $id === 'README' ? $outputRoot : $outputRoot . '/' . $id;
    if (!is_dir($directory)) { mkdir($directory, 0775, true); }
    file_put_contents($directory . '/index.html', layout($title, $content, navigationHtml($navigation, $labels, $id)));
}

$errors = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($outputRoot, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'html') { continue; }
    $html = (string) file_get_contents($file->getPathname());
    preg_match_all('/href="([^"]+)"/', $html, $matches);
    foreach ($matches[1] as $href) {
        if ($href === '' || str_starts_with($href, '#') || preg_match('#^(https?:|mailto:)#', $href)) { continue; }
        $path = parse_url($href, PHP_URL_PATH);
        if (!is_string($path)) { continue; }
        if ($path === '/') {
            $target = $outputRoot . '/index.html';
        } elseif (pathinfo($path, PATHINFO_EXTENSION) !== '') {
            $target = $outputRoot . '/' . ltrim($path, '/');
        } else {
            $target = $outputRoot . '/' . trim($path, '/') . '/index.html';
        }
        if (!is_file($target)) { $errors[] = $file->getPathname() . ' -> ' . $href; }
    }
}
if ($errors !== []) { throw new RuntimeException("Broken internal links:\n" . implode("\n", $errors)); }

printf("Built %d documentation pages into %s\n", count($known), $outputRoot);
