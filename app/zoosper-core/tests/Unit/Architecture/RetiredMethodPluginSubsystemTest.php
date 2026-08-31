<?php
declare(strict_types=1);
it('keeps the unused duplicate method-plugin subsystem retired', function (): void {
    $root=dirname(__DIR__,5);
    expect(is_dir($root.'/app/zoosper-core/src/Plugin'))->toBeFalse();
    $scan=[];
    foreach ([$root.'/app',$root.'/packages',$root.'/config',$root.'/tools'] as $base) {
        if (!is_dir($base)) continue;
        $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (!$file->isFile() || $file->getExtension() !== 'php' || str_contains($path, '/tests/')) continue;
            $source=(string)file_get_contents($file->getPathname());
            if (str_contains($source,'Zoosper\Core\Plugin') || str_contains($source,'method_plugins.php')) $scan[]=$path;
        }
    }
    expect($scan)->toBe([]);
    $decision=(string)file_get_contents($root.'/docs/architecture-decisions/marko-extensibility-ownership.md');
    expect($decision)->toContain('Phase 10BE removed the unused `Zoosper\Core\Plugin` subsystem')
        ->toContain('Do not recreate `Zoosper\Core\Plugin`');
});
it('records staged bridge ownership without pretending inactive dependencies are gone', function (): void {
    $root=dirname(__DIR__,5);
    $core=json_decode((string)file_get_contents($root.'/app/zoosper-core/composer.json'),true,512,JSON_THROW_ON_ERROR);
    expect($core['require'])->toHaveKey('zoosper/cache')->toHaveKey('zoosper/config')->not->toHaveKeys(['marko/cache','marko/cache-file','marko/cache-redis','marko/config','marko/encryption']);
    $decision=(string)file_get_contents($root.'/docs/architecture-decisions/marko-cache-config-encryption-ownership.md');
    expect($decision)->toContain('Phase 10BI extracted `zoosper/cache`')
        ->toContain('Phase 10BJ extracted `zoosper/config`')
        ->toContain('Encryption is not extracted independently');
});










