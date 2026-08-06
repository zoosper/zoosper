<?php

declare(strict_types=1);

it('audits only after successful save and clear calls without form payloads', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/app/zoosper-settings/src/Controller/SettingsCatalogueController.php');
    expect(strpos($source, '$this->writer->write('))->toBeLessThan(strpos($source, '$this->audit->sectionSaved('))
        ->and(strpos($source, '$this->clearer->clear('))->toBeLessThan(strpos($source, '$this->audit->overrideCleared('))
        ->and($source)->not->toContain('$this->audit->sectionSaved(' . '$form')
        ->not->toContain('$this->audit->overrideCleared(' . '$form');
});
