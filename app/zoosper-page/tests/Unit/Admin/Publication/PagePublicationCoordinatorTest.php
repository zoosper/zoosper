<?php

declare(strict_types=1);

use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Core\Event\EventListenerInterface;
use Zoosper\Page\Application\Publication\PagePublicationCoordinator;
use Zoosper\Page\Event\PageEvents;
use Zoosper\Page\Repository\PageRepository;

it('publishes and unpublishes with the established Page events', function (): void {
    $pdo = phase9fnPagesPdo();
    $repo = new PageRepository($pdo);
    $id = $repo->create(1, 'Page', 'page', 'Body');
    $events = new class implements EventDispatcherInterface {
        public array $names = [];
        public function listen(string $eventName, callable|EventListenerInterface $listener): self { return $this; }
        public function dispatch(string $eventName, object $event): object { $this->names[] = $eventName; return $event; }
        public function listeners(string $eventName): array { return []; }
    };
    $service = new PagePublicationCoordinator($repo, $events);
    $page = $repo->findById($id);
    $service->publish($page, 7);
    $service->unpublish($repo->findById($id), 7);
    expect($events->names)->toBe([PageEvents::PUBLISHED, PageEvents::UNPUBLISHED])
        ->and($repo->findById($id)?->status)->toBe('draft');
});










