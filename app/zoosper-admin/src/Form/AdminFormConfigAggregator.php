<?php

declare(strict_types=1);


/**
 * PHASE_140DF_ADMIN_FORM_CONFIG_LAYERED_LOADER
 * Runtime migration marker: approved to use ConfigFileLayeredLoader
 * for module-default plus root-override config resolution.
 */
namespace Zoosper\Admin\Form;



use Zoosper\Admin\Form\AdminConfigLayeredFileLoader;
use Zoosper\Core\Config\ConfigFileLayeredLoader;
/**
 * Aggregates admin form provider and processor configuration from modules.
 *
 * Empty handles are intentionally preserved. A module may declare an empty
 * processor list for a form handle to document that the extension point exists
 * even before any concrete processor classes are registered.
 */
final readonly class AdminFormConfigAggregator
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $rootConfig Runtime/root config already loaded
     *                                         by ConfigRepository, if available.
     *
     * @return array{
     *     forms: array<string, list<class-string<AdminFormSectionProviderInterface>>>,
     *     processors: array<string, list<class-string<AdminFormProcessorInterface>>>
     * }
     */
    public function aggregate(array $rootConfig = []): array
    {
        $forms = [];
        $processors = [];

        foreach ($this->configFiles() as $configFile) {
            $config = $this->readConfigFile($configFile);
            $forms = $this->mergeGroupedClasses($forms, $config['forms'] ?? []);
            $processors = $this->mergeGroupedClasses($processors, $config['processors'] ?? []);
        }

        $forms = $this->mergeGroupedClasses($forms, $rootConfig['forms'] ?? []);
        $processors = $this->mergeGroupedClasses($processors, $rootConfig['processors'] ?? []);

        return [
            'forms' => $forms,
            'processors' => $processors,
        ];
    }

    /** @return list<string> */
    private function configFiles(): array
    {
        $patterns = [
            $this->basePath . '/app/*/config/admin_forms.php',
            $this->basePath . '/modules/*/config/admin_forms.php',
            $this->basePath . '/modules/*/*/config/admin_forms.php',
            $this->basePath . '/vendor/*/*/config/admin_forms.php',
            $this->basePath . '/config/admin_forms.php',
        ];

        $files = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $file) {
                if (is_file($file)) {
                    $files[] = $file;
                }
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    /** @return array<string, mixed> */
    private function readConfigFile(string $file): array
    {
        $config = $this->loadLayeredAdminFormConfigFile('admin-form-config:' . basename((string) $file), (string) $file);

        return is_array($config) ? $config : [];
    }

    /**
     * @param array<string, list<class-string>> $current
     * @param mixed $incoming
     *
     * @return array<string, list<class-string>>
     */
    private function mergeGroupedClasses(array $current, mixed $incoming): array
    {
        if (!is_array($incoming)) {
            return $current;
        }

        foreach ($incoming as $formHandle => $classes) {
            if (!is_string($formHandle) || !is_array($classes)) {
                continue;
            }

            if (!isset($current[$formHandle])) {
                $current[$formHandle] = [];
            }

            foreach ($classes as $class) {
                if (!is_string($class) || $class === '') {
                    continue;
                }

                if (!in_array($class, $current[$formHandle], true)) {
                    $current[$formHandle][] = $class;
                }
            }
        }

        return $current;
    }

    /**
     * PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED
     * Load an admin form config file through the proven layered runtime bridge.
     *
     * @return array<string, mixed>
     */
    private function loadLayeredAdminFormConfigFile(string $source, string $path): array
    {
        return (new AdminConfigLayeredFileLoader())->load([$source => $path]);
    }
}
