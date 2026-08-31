<?php

declare(strict_types=1);

namespace Zoosper\Page\Console;

use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Repository\SiteRepository;

/**
 * `page:create` — creates a published page on an existing site.
 *
 * Console/kernel decoupling phase: relocated out of bin/zoosper, which
 * previously imported both SiteRepository and PageRepository directly.
 * zoosper-page already depends on zoosper-site in its own composer.json
 * (confirmed via Phase 1.40 composer.json audit), so this cross-module
 * dependency is legitimate and unchanged by this phase.
 */
final readonly class PageCreateCommand implements ConsoleCommandInterface
{
    public function __construct(
        private SiteRepository $sites,
        private PageRepository $pages,
    ) {
    }

    public function name(): string
    {
        return 'page:create';
    }

    public function description(): string
    {
        return "--site=main --title='Home' --slug=home --content='Welcome to Zoosper.'";
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);
        $siteCode = $options['site'] ?? 'main';

        try {
            $title = ConsoleOptions::required($options, 'title');
        } catch (\RuntimeException $exception) {
            $output->errorln($exception->getMessage());
            return 1;
        }

        $slug = $options['slug'] ?? ConsoleOptions::slugify($title);
        $content = $options['content'] ?? 'Hello from Zoosper.';

        $site = $this->sites->findByCode($siteCode);
        if ($site === null) {
            $output->errorln("Site does not exist: {$siteCode}");
            return 1;
        }

        $id = $this->pages->createPublished($site->id, $title, $slug, $content);
        $output->writeln("Created published page #{$id}: /{$slug}");

        return 0;
    }
}










