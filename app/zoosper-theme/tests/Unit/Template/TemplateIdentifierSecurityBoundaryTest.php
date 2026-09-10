<?php

declare(strict_types=1);

namespace Zoosper\Theme\Tests\Unit\Template;

it('validates every template identifier before candidate construction', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Template/TemplateRenderer.php');

    $expectedFragments = [
        <<<'SOURCE'
$this->assertSafeTemplateIdentifier($template);
SOURCE,
        <<<'SOURCE'
private function assertSafeTemplateIdentifier(string $template): void
SOURCE,
        <<<'SOURCE'
str_contains($template, "\0")
SOURCE,
        <<<'SOURCE'
str_contains($template, '\\')
SOURCE,
        <<<'SOURCE'
str_starts_with($template, '/')
SOURCE,
        <<<'SOURCE'
preg_match('/^[A-Za-z]:\//', $template)
SOURCE,
        <<<'SOURCE'
preg_match('#(^|/)\.{1,2}(/|$)#', $path)
SOURCE,
        <<<'SOURCE'
str_contains($path, '//')
SOURCE,
        <<<'SOURCE'
preg_match('/^[a-z0-9][a-z0-9_-]*$/', $parts[0])
SOURCE,
        <<<'SOURCE'
preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', $segment)
SOURCE,
    ];

    foreach ($expectedFragments as $fragment) {
        expect($source)->toContain($fragment);
    }
});

it('keeps validation ahead of theme module and executable engine resolution', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Template/TemplateRenderer.php');
    $validation = strpos($source, <<<'SOURCE'
$this->assertSafeTemplateIdentifier($template);
SOURCE);
    $moduleResolution = strpos($source, <<<'SOURCE'
return $this->resolveModuleTemplatePath($theme, $template);
SOURCE);
    $candidateConstruction = strpos($source, <<<'SOURCE'
'/templates/overrides/' . $variant
SOURCE);

    expect($validation)->not->toBeFalse()
        ->and($moduleResolution)->not->toBeFalse()
        ->and($candidateConstruction)->not->toBeFalse()
        ->and($validation)->toBeLessThan($moduleResolution)
        ->and($validation)->toBeLessThan($candidateConstruction);
});
