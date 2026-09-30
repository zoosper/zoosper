<?php
declare(strict_types=1);
namespace Zoosper\Core\Scaffold;
use Zoosper\Errors\ZoosperException;
/**
 * Creates a readable, non-registered API Grid package skeleton under packages/.
 *
 * @psalm-api
 */
final readonly class ApiGridScaffolder
{
    public function __construct(private string $basePath) {}
    public function scaffold(string $input, string $gridKey, string $route): ApiGridScaffoldResult
    {
        $identity = $this->identity($input);
        $gridKey = strtolower(trim($gridKey));
        $route = trim($route);
        if (preg_match('/^[a-z][a-z0-9_.-]*$/', $gridKey) !== 1) {
            throw $this->invalid('API Grid key must be a stable lowercase identifier.', ['grid_key' => $gridKey]);
        }
        if (preg_match('~^/admin/[a-z0-9/-]+$~', $route) !== 1) {
            throw $this->invalid('API Grid route must be an application-local /admin/ path.', ['route' => $route]);
        }
        $root = rtrim($this->basePath, '/\\') . '/packages/' . $identity['directory'];
        if (is_dir($root)) {
            throw $this->invalid('API Grid package already exists.', ['path' => $root]);
        }
        $files = $this->files($identity, $gridKey, $route);
        $created = [];
        try {
            foreach ($files as $relative => $contents) {
                $path = $root . '/' . $relative;
                if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) {
                    throw new \RuntimeException('Unable to create scaffold directory.');
                }
                if (file_put_contents($path, $contents, LOCK_EX) === false) {
                    throw new \RuntimeException('Unable to write scaffold file.');
                }
                $created[] = 'packages/' . $identity['directory'] . '/' . $relative;
            }
        } catch (\Throwable $exception) {
            $this->remove($root);
            throw $exception;
        }
        return new ApiGridScaffoldResult($identity['package'], $identity['module'], $identity['namespace'], $root, $gridKey, $route, $created);
    }
    /** @return array{package:string,directory:string,module:string,namespace:string,prefix:string} */
    private function identity(string $input): array
    {
        $input = trim($input);
        if (preg_match('~^[A-Za-z][A-Za-z0-9]*/[A-Za-z][A-Za-z0-9-]*$~', $input) !== 1) {
            throw $this->invalid('API Grid package name must look like Vendor/Module.', ['input' => $input]);
        }
        $parts = explode('/', $input, 2);
        if (count($parts) !== 2) {
            throw $this->invalid('API Grid package name must contain vendor and module parts.', ['input' => $input]);
        }
        [$vendor, $module] = $parts;
        $vendorPackage = strtolower($vendor);
        $modulePackage = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', str_replace('-', '', $module)));
        $vendorClass = ucfirst(strtolower($vendor));
        $parts = array_filter(explode('-', $modulePackage));
        $moduleClass = implode('', array_map(static fn (string $part): string => ucfirst($part), $parts));
        return ['package'=>$vendorPackage.'/'.$modulePackage,'directory'=>$vendorPackage.'-'.$modulePackage,'module'=>$vendorClass.'_'.$moduleClass,'namespace'=>$vendorClass.'\\'.$moduleClass.'\\','prefix'=>$moduleClass];
    }
    /** @param array{package:string,directory:string,module:string,namespace:string,prefix:string} $id @return array<string,string> */
    private function files(array $id, string $gridKey, string $route): array
    {
        $ns = rtrim($id['namespace'], '\\');
        $escaped = str_replace('\\', '\\\\', $id['namespace']);
        $prefix = $id['prefix'];
        $permission = str_replace(['.', '-'], '_', $gridKey) . '.view';
        $composer = json_encode(['name'=>$id['package'],'description'=>$id['module'].' API Grid integration for Zoosper CMS.','type'=>'zoosper-module','license'=>'MIT','require'=>['php'=>'^8.5','zoosper/api-grid'=>'^0.3.1@alpha','zoosper/grid'=>'^0.3.1@alpha'],'require-dev'=>['pestphp/pest'=>'^3.0','pestphp/pest-plugin'=>'^3.0','phpunit/phpunit'=>'^11.0'],'autoload'=>['psr-4'=>[$escaped=>'src/']],'autoload-dev'=>['psr-4'=>[$escaped.'Tests\\'=>'tests/']]], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
        return [
            'composer.json'=>$composer,
            'module.php'=>"<?php\ndeclare(strict_types=1);\nreturn [];\n",
            '.gitattributes'=>"/tests export-ignore\n/phpunit.xml.dist export-ignore\n/.gitignore export-ignore\n/.github export-ignore\n",
            '.gitignore'=>"/vendor/\n/.phpunit.cache/\n/coverage/\ncomposer.lock\n.DS_Store\n",
            'config/admin_routes.php'=>"<?php\ndeclare(strict_types=1);\n// Enable only after deployment-owned endpoint and permission wiring are complete.\nreturn [];\n",
            'config/admin_menu.php'=>"<?php\ndeclare(strict_types=1);\nreturn [];\n",
            'config/controllers.php'=>"<?php\ndeclare(strict_types=1);\nreturn [];\n",
            'config/services.php'=>"<?php\ndeclare(strict_types=1);\nreturn [];\n",
            'src/Api/'.$prefix.'RequestMapper.php'=>"<?php\ndeclare(strict_types=1);\nnamespace {$ns}\\Api;\nuse Zoosper\\ApiGrid\\Mapping\\ApiGridContext;\nuse Zoosper\\ApiGrid\\Mapping\\ApiGridRequestMapperInterface;\nuse Zoosper\\ApiGrid\\Transport\\ApiRequest;\nuse Zoosper\\Grid\\DataSource\\GridQuery;\nfinal class {$prefix}RequestMapper implements ApiGridRequestMapperInterface\n{\n    public function map(GridQuery \$query, ApiGridContext \$context): ApiRequest\n    {\n        // Map only capability-supported query state and deployment-owned context.\n        return new ApiRequest('GET', '/replace-with-endpoint');\n    }\n}\n",
            'src/Api/'.$prefix.'ResponseMapper.php'=>"<?php\ndeclare(strict_types=1);\nnamespace {$ns}\\Api;\nuse Zoosper\\ApiGrid\\Mapping\\ApiGridResponseMapperInterface;\nuse Zoosper\\ApiGrid\\Mapping\\ApiGridResponseMappingException;\nuse Zoosper\\ApiGrid\\Transport\\ApiResponse;\nuse Zoosper\\Grid\\DataSource\\GridQuery;\nuse Zoosper\\Grid\\DataSource\\GridResult;\nfinal class {$prefix}ResponseMapper implements ApiGridResponseMapperInterface\n{\n    public function map(ApiResponse \$response, GridQuery \$query): GridResult\n    {\n        throw new ApiGridResponseMappingException('Implement the endpoint-specific response envelope before enabling this integration.');\n    }\n}\n",
            'tests/Fixtures/ApiResponseFixtures.php'=>"<?php\ndeclare(strict_types=1);\nnamespace {$ns}\\Tests\\Fixtures;\nuse Zoosper\\ApiGrid\\Testing\\ApiResponseFixture;\nuse Zoosper\\ApiGrid\\Transport\\ApiResponse;\nfinal class ApiResponseFixtures\n{\n    public static function valid(): ApiResponse { return ApiResponseFixture::success(['records' => []]); }\n    public static function malformed(): ApiResponse { return ApiResponseFixture::success(['unexpected' => true]); }\n    private function __construct() {}\n}\n",            'tests/Unit/'.$prefix.'ApiGridScaffoldTest.php'=>"<?php\ndeclare(strict_types=1);\nnamespace {$ns}\\Tests\\Unit;\nuse {$ns}\\Tests\\Fixtures\\ApiResponseFixtures;\nit('keeps generated API Grid integration disabled until endpoint mapping is implemented', function (): void {\n    \$root = dirname(__DIR__, 2);\n    expect(require \$root . '/config/admin_routes.php')->toBe([])\n        ->and(ApiResponseFixtures::valid()->statusCode)->toBe(200)\n        ->and((string) file_get_contents(\$root . '/src/Api/{$prefix}ResponseMapper.php'))->toContain('Implement the endpoint-specific response envelope');\n});\n",            'phpunit.xml.dist'=>"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<phpunit bootstrap=\"vendor/autoload.php\" colors=\"true\"><testsuites><testsuite name=\"Unit\"><directory>tests/Unit</directory></testsuite></testsuites></phpunit>\n",
            'README.md'=>"# {$id['module']}\n\nGenerated API Grid integration scaffold for key `{$gridKey}` and proposed route `{$route}`.\n\n## Responsibilities\nImplement endpoint-specific request, response and row mapping behind `zoosper/api-grid`.\n\n## Architecture\nThe scaffold is intentionally disabled until trusted configuration, permission, controller and presentation wiring are completed.\n\n## Configuration\nBase URLs, credentials and tenant scope must be deployment-owned and must never come from browser query state.\n\n## Dependencies\nRequires `zoosper/api-grid` and `zoosper/grid` on the bounded first-party alpha release train.\n\n## Extension points\nReplace the generated mappers and add authentication through existing API Grid contracts.\n\n## Testing\nRun `php8.5 vendor/bin/pest packages/{$id['directory']}/tests` and `zcomposer test`.\n\n## Operational notes\nKeep remote failures distinct from empty results. Never log payloads, credentials, personal data or transactional data. Proposed permission: `{$permission}`.\n",
        ];
    }
    private function invalid(string $message, array $details): ZoosperException
    {
        return new ZoosperException(message:$message, context:'The API Grid generator validates package identity and local Admin ownership before writing files.', suggestion:'Use `php bin/zoosper make:api-grid Acme/RemoteRecords --key=acme.remote-records --route=/admin/remote-records`.', docsUrl:'docs/developer-guide.md', details:$details);
    }
    private function remove(string $path): void
    {
        if (!is_dir($path)) return;
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) { $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()); }
        rmdir($path);
    }
}
