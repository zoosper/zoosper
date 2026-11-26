<?php

declare(strict_types=1);

namespace Zoosper\Theme\Tests\Unit\Template;

use ReflectionMethod;
use RuntimeException;
use Zoosper\Theme\Layout\LayoutUpdateRepository;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\Theme;
use Zoosper\Theme\Theme\ThemeResolver;

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

it('resolves module templates while rejecting traversal before looking up files', function (): void {
    $resolver = new ThemeResolver(dirname(__DIR__, 5) . '/themes');
    $renderer = new TemplateRenderer($resolver);
    $resolve = new ReflectionMethod(TemplateRenderer::class, 'resolveTemplatePath');
    $theme = $resolver->resolve();

    expect($resolve->invoke($renderer, $theme, 'zoosper-page::page/view.php'))
        ->toEndWith('/templates/modules/zoosper-page/page/view.php');

    expect(fn () => $resolve->invoke($renderer, $theme, 'zoosper-page::../layout.php'))
        ->toThrow(RuntimeException::class, 'without traversal segments');
});

it('preserves template replacement, removal, and the no-update fallback', function (): void {
    $resolver = new ThemeResolver(dirname(__DIR__, 2) . '/Fixtures/themes');
    $plain = new TemplateRenderer($resolver);
    $withUpdates = new TemplateRenderer($resolver, layoutUpdates: new LayoutUpdateRepository());

    expect($plain->render('original.php', handle: 'test'))->toBe('original')
        ->and($withUpdates->render('original.php', handle: 'test'))->toBe('replacement')
        ->and($withUpdates->render('original.php', handle: 'other'))->toBe('original')
        ->and($withUpdates->render('removed.php', handle: 'test'))->toBe('');
});

it('uses normalized theme paths for ordinary and module templates', function (): void {
    $root = dirname(__DIR__, 5) . '/themes';
    $resolver = new ThemeResolver($root);
    $renderer = new TemplateRenderer($resolver);
    $theme = new Theme('default', $root . '/default/');
    $resolve = new ReflectionMethod(TemplateRenderer::class, 'resolveTemplatePath');

    expect($theme->templatePath('/layout.php'))->toBe($root . '/default/templates/layout.php')
        ->and($resolve->invoke($renderer, $theme, 'layout.php'))->toBe($theme->templatePath('layout.php'))
        ->and($resolve->invoke($renderer, $theme, 'zoosper-page::page/view.php'))
        ->toBe($theme->templatePath('modules/zoosper-page/page/view.php'));
});
