<?php

declare(strict_types=1);

it('keeps Menu URL placeholders as plain attribute text', function (): void {
    $root = dirname(__DIR__, 3);
    $template = (string) file_get_contents($root . '/resources/views/admin/menu/edit.latte');

    expect($template)
        ->not->toContain('<a href=')
        ->not->toContain('fai-ChatInputEntity__text')
        ->and(substr_count($template, 'placeholder="https://example.com or /relative-path"'))
        ->toBe(2);
});
