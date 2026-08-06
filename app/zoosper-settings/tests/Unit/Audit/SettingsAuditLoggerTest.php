<?php

declare(strict_types=1);

use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsSection;

final class RecordingSettingsAuditLogger implements AuditLoggerInterface
{
    public array $events = [];
    public function logAction(?int $actorAdminUserId, ?string $actorEmail, string $action, string $entityType, ?string $entityId, string $summary, array $metadata = []): void
    {
        $this->events[] = compact('actorAdminUserId', 'actorEmail', 'action', 'entityType', 'entityId', 'summary', 'metadata');
    }
}

it('records value-free save and clear metadata', function (): void {
    $recording = new RecordingSettingsAuditLogger();
    $audit = new SettingsAuditLogger($recording);
    $section = new SettingsSection('settings.catalogue', 'Catalogue', 'advanced', 'settings', [
        new SettingDefinition('settings.catalogue.show_paths', 'Show paths', 'boolean'),
        new SettingDefinition('settings.catalogue.secret', 'Secret', 'secret', secret: true),
    ]);
    $audit->sectionSaved(7, 'admin@example.test', $section, 'website', 'main', ['settings.catalogue.show_paths', 'settings.catalogue.secret', 'unknown.path']);
    $audit->overrideCleared(7, 'admin@example.test', $section, 'website', 'main', 'settings.catalogue.show_paths');
    expect($recording->events)->toHaveCount(2)
        ->and($recording->events[0]['action'])->toBe('settings.section.saved')
        ->and($recording->events[0]['metadata']['paths'])->toBe(['settings.catalogue.show_paths'])
        ->and($recording->events[0]['metadata'])->not->toHaveKey('values')
        ->and($recording->events[1]['action'])->toBe('settings.override.cleared');
});

it('is optional and best effort', function (): void {
    $section = new SettingsSection('settings.catalogue', 'Catalogue', 'advanced', 'settings', []);
    expect(fn () => (new SettingsAuditLogger(null))->sectionSaved(null, null, $section, 'default', '', []))->not->toThrow(\Throwable::class);
});
