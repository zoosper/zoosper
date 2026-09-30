<?php
declare(strict_types=1);
namespace Zoosper\Core\Tests\Unit\Scaffold;
use Zoosper\Core\Scaffold\ApiGridScaffolder;
use Zoosper\Errors\ZoosperException;
function apiGridScaffoldRoot(): string { $root=sys_get_temp_dir().'/zoosper-api-grid-scaffold-'.bin2hex(random_bytes(5));mkdir($root.'/packages',0775,true);return $root; }
it('scaffolds a disabled readable API Grid package with fixtures and bounded dependencies', function (): void {
    $root=apiGridScaffoldRoot();$result=(new ApiGridScaffolder($root))->scaffold('Acme/RemoteRecords','acme.remote-records','/admin/remote-records');
    $package=$root.'/packages/acme-remote-records';$composer=json_decode((string)file_get_contents($package.'/composer.json'),true,512,JSON_THROW_ON_ERROR);
    expect($result->packageName)->toBe('acme/remote-records')->and($result->moduleName)->toBe('Acme_RemoteRecords')->and($result->namespace)->toBe('Acme\\RemoteRecords\\')->and($result->packagePath)->toBe($package)->and($result->gridKey)->toBe('acme.remote-records')
        ->and($composer['type'])->toBe('zoosper-module')->and($composer['require']['zoosper/api-grid'])->toBe('^0.3.1@alpha')
        ->and(require $package.'/config/admin_routes.php')->toBe([])
        ->and($package.'/tests/Fixtures/ApiResponseFixtures.php')->toBeFile()
        ->and((string)file_get_contents($package.'/README.md'))->toContain('## Operational notes')->toContain('zcomposer test');
});
it('rejects unsafe identity key and route before creating a directory', function (string $name,string $key,string $route): void {
    $root=apiGridScaffoldRoot();
    expect(fn()=>(new ApiGridScaffolder($root))->scaffold($name,$key,$route))->toThrow(ZoosperException::class)
        ->and(glob($root.'/packages/*'))->toBe([]);
})->with([['bad','acme.records','/admin/records'],['Acme/Records','Bad Key','/admin/records'],['Acme/Records','acme.records','https://evil.test/admin/records']]);
it('exposes make api grid through the thin executable', function (): void {
    $root=dirname(__DIR__,5);$source=(string)file_get_contents($root.'/bin/zoosper');
    expect($source)->toContain('make:api-grid')->toContain('new MakeApiGridCommand(')->toContain('new ApiGridScaffolder(');
});
