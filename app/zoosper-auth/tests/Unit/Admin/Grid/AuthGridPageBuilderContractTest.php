<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridPageBuilder;
use Zoosper\Auth\Admin\Grid\AuthGridPage;
use Zoosper\Auth\Admin\Grid\RoleGridPageBuilder;

it('requires authenticated identity for both Auth Grid page builders', function (): void {
    foreach ([AdminUserGridPageBuilder::class, RoleGridPageBuilder::class] as $builder) {
        $method = new \ReflectionMethod($builder, 'build');
        $parameters = $method->getParameters();

        expect($parameters[0]->getName())->toBe('authenticatedAdminUserId')
            ->and((string) $parameters[0]->getType())->toBe('int')
            ->and((string) $method->getReturnType())->toBe(AuthGridPage::class);
    }
});

it('uses resolved Grid state for rows and table rendering', function (): void {
    $root = dirname(__DIR__, 6);

    foreach (['AdminUserGridPageBuilder.php', 'RoleGridPageBuilder.php'] as $file) {
        $source = (string) file_get_contents(
            $root . '/app/zoosper-auth/src/Admin/Grid/' . $file,
        );

        expect($source)->toContain('$resolved[\'state\']')
            ->toContain('$this->dataSource->paginate($state->criteria)')
            ->toContain('$this->renderer->renderBody(')
            ->not->toContain('$_GET')
            ->not->toContain('$_POST')
            ->not->toContain('password')
            ->not->toContain('permission');
    }
});

it('keeps Auth Grid page output typed and framework-neutral', function (): void {
    $constructor = (new \ReflectionClass(AuthGridPage::class))->getConstructor();
    $names = array_map(
        static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
        $constructor?->getParameters() ?? [],
    );

    expect($names)->toBe([
        'title',
        'workspaceHtml',
        'gridHtml',
        'state',
        'pagination',
    ]);
});










