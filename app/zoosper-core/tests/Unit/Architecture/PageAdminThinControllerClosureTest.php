<?php

declare(strict_types=1);

it('moves save normalisation lifecycle persistence and publication events out of PageAdminController', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $factory = (string) file_get_contents($root . '/app/zoosper-page/config/controllers.php');

    expect($controller)->toContain('PageSaveCoordinator')->toContain('PagePublicationCoordinator')
        ->not->toContain('private function normaliseSlug')
        ->not->toContain('private function normaliseContentJson')
        ->not->toContain('private function runEntitySave')
        ->not->toContain('PageEvents::PUBLISHED')
        ->not->toContain('PageEvents::UNPUBLISHED')
        ->not->toContain('$this->pages->publish(')
        ->not->toContain('$this->pages->unpublish(')
        ->and($factory)->toContain('$services->get(PageSaveCoordinator::class)')->toContain('new PagePublicationCoordinator(');
});










