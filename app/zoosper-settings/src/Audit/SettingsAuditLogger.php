<?php

declare(strict_types=1);

namespace Zoosper\Settings\Audit;

use Throwable;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Settings\Definition\SettingsSection;

/** Best-effort, value-free audit events for successful settings mutations. */
final readonly class SettingsAuditLogger
{
    public function __construct(private ?AuditLoggerInterface $audit)
    {
    }

    /** @param list<string> $paths */
    public function sectionSaved(?int $actorId, ?string $actorEmail, SettingsSection $section, string $scopeType, string $scopeKey, array $paths): void
    {
        $this->record(
            actorId: $actorId,
            actorEmail: $actorEmail,
            action: 'settings.section.saved',
            entityId: $section->id,
            summary: 'Saved settings section ' . $section->id,
            metadata: $this->metadata($section, $scopeType, $scopeKey, $paths),
        );
    }

    public function overrideCleared(?int $actorId, ?string $actorEmail, SettingsSection $section, string $scopeType, string $scopeKey, string $path): void
    {
        $this->record(
            actorId: $actorId,
            actorEmail: $actorEmail,
            action: 'settings.override.cleared',
            entityId: $section->id,
            summary: 'Cleared settings override for ' . $path,
            metadata: $this->metadata($section, $scopeType, $scopeKey, [$path]),
        );
    }

    /** @param list<string> $paths @return array{section:string,scope_type:string,scope_key:?string,paths:list<string>} */
    private function metadata(SettingsSection $section, string $scopeType, string $scopeKey, array $paths): array
    {
        $declared = [];
        foreach ($section->settings as $definition) {
            if (!$definition->secret && in_array($definition->path, $paths, true)) {
                $declared[] = $definition->path;
            }
        }
        sort($declared);

        return [
            'section' => $section->id,
            'scope_type' => $scopeType,
            'scope_key' => $scopeType === 'default' ? null : $scopeKey,
            'paths' => array_values(array_unique($declared)),
        ];
    }

    /** @param array<string, mixed> $metadata */
    private function record(?int $actorId, ?string $actorEmail, string $action, string $entityId, string $summary, array $metadata): void
    {
        if ($this->audit === null) {
            return;
        }
        try {
            $this->audit->logAction(
                actorAdminUserId: $actorId,
                actorEmail: $actorEmail,
                action: $action,
                entityType: 'settings_section',
                entityId: $entityId,
                summary: $summary,
                metadata: $metadata,
            );
        } catch (Throwable) {
            // The mutation has committed; audit remains best-effort by platform policy.
        }
    }
}










