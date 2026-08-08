<?php

declare(strict_types=1);

namespace Zoosper\Settings\Admin;

use InvalidArgumentException;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Config\Scope\ScopeType;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Definition\SettingsSection;
use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Write\ScopedSettingClearer;
use Zoosper\Settings\Write\SectionSettingsWriter;
use Zoosper\Settings\Write\SettingValidationException;

/** Owns validated Settings save and inherited-value restoration workflows. */
final readonly class SettingsMutationCoordinator
{
    public function __construct(
        private ModuleSettingsCatalogueLoader $catalogue,
        private ScopedSettingValueResolver $values,
        private SettingsScopeSelection $scopeSelection,
        private SectionSettingsWriter $writer,
        private ScopedSettingClearer $clearer,
        private SettingsAuditLogger $audit,
        private FlashMessageStoreInterface $flash,
        private SettingsAdminUrls $urls,
    ) {
    }

    public function save(Request $request, AdminUser $actor): Response
    {
        $form = $request->form();
        $selection = $this->scopeSelection->select(
            (string) ($form['scope'] ?? 'default'),
            (string) ($form['scope_key'] ?? ''),
        );
        $section = $this->findSection((string) ($form['section'] ?? ''));
        $settings = is_array($form['settings'] ?? null) ? $form['settings'] : [];

        foreach ($section->settings as $definition) {
            $effective = $this->values->resolve($definition, $selection['context']);
            if (!$definition->readOnly && !$definition->secret && $effective->source === 'project') {
                throw new SettingValidationException([
                    $definition->path => 'Project-controlled settings cannot be overridden here.',
                ]);
            }
        }

        try {
            [$scopeType, $resolvedKey] = self::writeScope($selection['type'], $selection['key']);
            $this->writer->write($section, $scopeType, $resolvedKey, $settings);
            $this->audit->sectionSaved(
                $actor->id,
                $actor->email,
                $section,
                $selection['type'],
                $selection['key'],
                array_keys($settings),
            );
            $this->flash->success('Settings saved.', 'settings.saved');
        } catch (SettingValidationException $exception) {
            $this->flash->error(implode(' ', array_values($exception->errors)), 'settings.validation');
        }

        return Response::redirect($this->urls->scope($selection['type'], $selection['key']));
    }

    public function clear(Request $request, AdminUser $actor): Response
    {
        $form = $request->form();
        $selection = $this->scopeSelection->select(
            (string) ($form['scope'] ?? 'default'),
            (string) ($form['scope_key'] ?? ''),
        );
        $section = $this->findSection((string) ($form['section'] ?? ''));
        [$scopeType, $resolvedKey] = self::writeScope($selection['type'], $selection['key']);

        try {
            $path = (string) ($form['path'] ?? '');
            $this->clearer->clear($section, $path, $scopeType, $resolvedKey);
            $this->audit->overrideCleared(
                $actor->id,
                $actor->email,
                $section,
                $selection['type'],
                $selection['key'],
                $path,
            );
            $this->flash->success('Setting override cleared.', 'settings.cleared');
        } catch (SettingValidationException|InvalidArgumentException $exception) {
            $this->flash->error($exception->getMessage(), 'settings.clear.error');
        }

        return Response::redirect($this->urls->scope($selection['type'], $selection['key']));
    }

    private function findSection(string $id): SettingsSection
    {
        foreach ($this->catalogue->load()->all() as $section) {
            if ($section->id === $id) {
                return $section;
            }
        }

        throw new InvalidArgumentException("Unknown settings section: {$id}");
    }

    /** @return array{ScopeType, ?string} */
    private static function writeScope(string $type, string $key): array
    {
        return match ($type) {
            'default' => [ScopeType::Default, null],
            'website' => [ScopeType::Website, $key],
            'store' => [ScopeType::Store, $key],
            'site' => [ScopeType::Site, $key],
            default => throw new InvalidArgumentException("Unsupported settings scope: {$type}"),
        };
    }
}
