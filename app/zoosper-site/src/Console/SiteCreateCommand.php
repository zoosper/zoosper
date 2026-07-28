<?php

declare(strict_types=1);

namespace Zoosper\Site\Console;

use RuntimeException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Site\Repository\SiteRepository;

/**
 * `site:create` — creates a new site with its primary domain.
 *
 * Console/kernel decoupling phase: relocated out of bin/zoosper, which
 * previously imported SiteRepository directly. The env('DEFAULT_SITE_*', ...)
 * fallback calls are preserved exactly as before — env() is a global
 * function loaded during bootstrap, reachable from any namespace via PHP's
 * function-fallback rule, so behaviour is unchanged.
 */
final readonly class SiteCreateCommand implements ConsoleCommandInterface
{
    public function __construct(private SiteRepository $sites)
    {
    }

    public function name(): string
    {
        return 'site:create';
    }

    public function description(): string
    {
        return "--code=main --name='Main Website' --host=127.0.0.1";
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);

        $code = $options['code'] ?? (string) env('DEFAULT_SITE_CODE', 'main');
        $name = $options['name'] ?? (string) env('DEFAULT_SITE_NAME', 'Main Website');
        $host = $options['host'] ?? (string) env('DEFAULT_SITE_HOST', '127.0.0.1');
        $homepageSlug = $options['homepage'] ?? 'home';

        try {
            $id = $this->sites->create($code, $name, $host, $homepageSlug);
        } catch (RuntimeException $exception) {
            $output->errorln($exception->getMessage());
            return 1;
        }

        $output->writeln("Created site #{$id}: {$code} ({$host})");

        return 0;
    }
}
