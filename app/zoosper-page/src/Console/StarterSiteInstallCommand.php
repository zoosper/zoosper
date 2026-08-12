<?php

declare(strict_types=1);

namespace Zoosper\Page\Console;

use RuntimeException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Installs the compact, idempotent starter content required by the 0.2 alpha.
 * Existing Sites and Pages are retained unchanged; only missing records are created.
 */
final readonly class StarterSiteInstallCommand implements ConsoleCommandInterface
{
    public function __construct(
        private SiteRepository $sites,
        private PageRepository $pages,
    ) {
    }

    public function name(): string
    {
        return 'starter:install';
    }

    public function description(): string
    {
        return '--site=main --name="Main Website" --host=127.0.0.1';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);
        $siteCode = $options['site'] ?? 'main';
        $siteName = $options['name'] ?? 'Main Website';
        $host = $options['host'] ?? '127.0.0.1';

        try {
            $site = $this->sites->findByCode($siteCode);
            if ($site === null) {
                $siteId = $this->sites->create($siteCode, $siteName, $host, 'home', 'default');
                $site = $this->sites->findById($siteId);
                if ($site === null) {
                    throw new RuntimeException('Starter Site was created but could not be reloaded.');
                }
                $output->writeln("Created starter Site #{$site->id}: {$siteCode}");
            } else {
                $output->writeln("Retained existing Site #{$site->id}: {$siteCode}");
            }

            $pages = [
                'home' => [
                    'title' => 'Welcome',
                    'content' => '<p>Your Zoosper site is ready. Create pages, organise navigation, upload media, and publish with confidence.</p>',
                    'metaTitle' => 'Welcome',
                    'metaDescription' => 'Welcome to your Zoosper website.',
                ],
                'about' => [
                    'title' => 'About',
                    'content' => '<p>Use this page to introduce your organisation, purpose, and the people behind it.</p>',
                    'metaTitle' => 'About',
                    'metaDescription' => 'About this website.',
                ],
            ];

            foreach ($pages as $slug => $definition) {
                $existing = $this->pages->findPublishedBySlug($site->id, $slug);
                if ($existing !== null) {
                    $output->writeln("Retained existing published Page #{$existing->id}: /{$slug}");
                    continue;
                }
                $id = $this->pages->createPublished(
                    siteId: $site->id,
                    title: $definition['title'],
                    slug: $slug,
                    content: $definition['content'],
                    metaTitle: $definition['metaTitle'],
                    metaDescription: $definition['metaDescription'],
                );
                $output->writeln("Created starter Page #{$id}: /{$slug}");
            }
        } catch (\Throwable $exception) {
            $output->errorln('Starter installation failed: ' . $exception->getMessage());
            return 1;
        }

        $output->writeln('Starter content is ready. Re-running this command is safe.');
        return 0;
    }
}
