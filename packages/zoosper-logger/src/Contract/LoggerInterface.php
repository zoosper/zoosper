<?php
declare(strict_types=1);
namespace Zoosper\Logger\Contract;
use Throwable;
/** Public logging contract implemented by runtime logger adapters.
 * @psalm-api
 */
interface LoggerInterface
{
    public function emergency(string $message, array $context = []): void;
    public function alert(string $message, array $context = []): void;
    public function critical(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function notice(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function debug(string $message, array $context = []): void;
    public function exception(Throwable $exception, array $context = []): void;
}











