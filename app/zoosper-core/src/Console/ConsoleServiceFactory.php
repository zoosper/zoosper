<?php
declare(strict_types=1);
namespace Zoosper\Core\Console;
use Marko\Config\ConfigRepositoryInterface;
use PDO;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Config\Bridge\MarkoConfigRepositoryAdapter;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Container\ServiceProviderLoader;
use Zoosper\Core\Database\PdoConnectionProvider;
use Zoosper\Core\Error\ErrorHandler;
use Zoosper\Core\Http\ProductionSecurityPolicy;
use Zoosper\Logger\Manager\LogManager;
use Zoosper\Core\Module\ModuleRegistry;
final readonly class ConsoleServiceFactory
{
    public function __construct(private string $basePath, private ConfigRepository $config, private ModuleRegistry $modules, private PdoConnectionProvider $connection, private LogManager $logs, private ErrorHandler $errors) { ProductionSecurityPolicy::assertEnvironment(); }
    public function create(): ServiceContainer
    {
        $services = new ServiceContainer();
        $services->set(ConfigRepository::class, $this->config);
        $services->set(ConfigRepositoryInterface::class, new MarkoConfigRepositoryAdapter($this->config));
        $services->set(ModuleRegistry::class, $this->modules);
        $services->factory(PDO::class, fn (): PDO => $this->connection->get());
        $services->set(LogManager::class, $this->logs);
        $services->set(ErrorHandler::class, $this->errors);
        $services->set('logger.default', $this->logs->default());
        $services->set('logger.exception', $this->logs->exceptions());
        (new ServiceProviderLoader($this->modules, $services))->register();
        return $services;
    }
}
