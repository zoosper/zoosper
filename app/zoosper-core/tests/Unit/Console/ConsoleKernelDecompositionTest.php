<?php
declare(strict_types=1);
it('moves operational command behaviour out of the executable', function (): void {
    $root = dirname(__DIR__, 5); $bin = (string) file_get_contents($root . '/bin/zoosper');
    foreach (['MigrateCommand', 'CompileCommand', 'CacheClearCommand', 'ManifestStatusCommand', 'ManifestCheckCommand'] as $class) {
        expect($root . '/app/zoosper-core/src/Console/BuiltIn/' . $class . '.php')->toBeFile();
        expect($bin)->toContain('new ' . $class . '(');
    }
    expect($bin)->not->toContain("if (\$command === 'migrate')")
        ->not->toContain("if (\$command === 'compile')")
        ->not->toContain("if (\$command === 'cache:clear')");
});
it('provides reusable kernel and service-composition boundaries', function (): void {
    $root = dirname(__DIR__, 5);
    expect($root . '/app/zoosper-core/src/Console/ConsoleKernel.php')->toBeFile()
        ->and($root . '/app/zoosper-core/src/Console/ConsoleServiceFactory.php')->toBeFile();
});










