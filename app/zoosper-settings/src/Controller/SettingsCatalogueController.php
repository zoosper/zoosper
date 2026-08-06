<?php

declare(strict_types=1);

namespace Zoosper\Settings\Controller;

use InvalidArgumentException;
use RuntimeException;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Config\Scope\ScopeType;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Definition\SettingsSection;
use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Write\SectionSettingsWriter;
use Zoosper\Settings\Write\ScopedSettingClearer;
use Zoosper\Settings\Write\SettingValidationException;

final readonly class SettingsCatalogueController
{
    public function __construct(
        private SessionGuard $guard,
        private AdminViewRendererInterface $views,
        private ModuleSettingsCatalogueLoader $catalogue,
        private ScopedSettingValueResolver $values,
        private SettingsScopeSelection $scopeSelection,
        private SectionSettingsWriter $writer,
        private ScopedSettingClearer $clearer,
        private SettingsAuditLogger $audit,
        private CsrfTokenManager $csrf,
        private FlashMessageStoreInterface $flash,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $selection = $this->scopeSelection->fromRequest($request);
        $sections = $this->catalogue->load()->all();
        $categories = [];
        $effectiveValues = [];
        foreach ($sections as $section) {
            $categories[$section->category][] = $section;
            foreach ($section->settings as $setting) {
                $effectiveValues[$setting->path] = $this->values->resolve($setting, $selection['context']);
            }
        }

        return Response::html($this->views->render(
            title: 'Settings',
            template: 'zoosper-settings::admin/settings/index',
            data: [
                'categories' => $categories,
                'sectionCount' => count($sections),
                'effectiveValues' => $effectiveValues,
                'scopeLabel' => $selection['label'],
                'scopeType' => $selection['type'],
                'scopeKey' => $selection['key'],
                'websiteOptions' => $selection['websites'],
                'storeOptions' => $selection['stores'],
                'siteOptions' => $selection['sites'],
                'csrfToken' => $this->csrf->token(),
                'showPaths' => $this->isEnabled($effectiveValues['settings.catalogue.show_paths']->value ?? true),
            ],
            user: $user,
            active: 'settings',
        ));
    }

    public function save(Request $request): Response
    {
        $this->currentAdminUser();
        $form = $request->form();
        $scope = (string) ($form['scope'] ?? 'default');
        $scopeKey = (string) ($form['scope_key'] ?? '');
        $sectionId = (string) ($form['section'] ?? '');
        $selection = $this->scopeSelection->select($scope, $scopeKey);
        $section = $this->findSection($sectionId);
        $settings = is_array($form['settings'] ?? null) ? $form['settings'] : [];

        foreach ($section->settings as $definition) {
            $effective = $this->values->resolve($definition, $selection['context']);
            if (!$definition->readOnly && !$definition->secret && $effective->source === 'project') {
                throw new SettingValidationException([$definition->path => 'Project-controlled settings cannot be overridden here.']);
            }
        }

        try {
            [$scopeType, $resolvedKey] = $this->writeScope($selection['type'], $selection['key']);
            $this->writer->write($section, $scopeType, $resolvedKey, $settings);
            $actor = $this->currentAdminUser();
            $this->audit->sectionSaved($actor->id, $actor->email, $section, $selection['type'], $selection['key'], array_keys($settings));
            $this->flash->success('Settings saved.', 'settings.saved');
        } catch (SettingValidationException $exception) {
            $this->flash->error(implode(' ', array_values($exception->errors)), 'settings.validation');
        }

        return Response::redirect($this->scopeUrl($selection['type'], $selection['key']));
    }

    public function clear(Request $request): Response
    {
        $this->currentAdminUser();
        $form = $request->form();
        $selection = $this->scopeSelection->select(
            (string) ($form['scope'] ?? 'default'),
            (string) ($form['scope_key'] ?? ''),
        );
        $section = $this->findSection((string) ($form['section'] ?? ''));
        [$scopeType, $resolvedKey] = $this->writeScope($selection['type'], $selection['key']);

        try {
            $path = (string) ($form['path'] ?? '');
            $this->clearer->clear($section, $path, $scopeType, $resolvedKey);
            $actor = $this->currentAdminUser();
            $this->audit->overrideCleared($actor->id, $actor->email, $section, $selection['type'], $selection['key'], $path);
            $this->flash->success('Setting override cleared.', 'settings.cleared');
        } catch (SettingValidationException|InvalidArgumentException $exception) {
            $this->flash->error($exception->getMessage(), 'settings.clear.error');
        }

        return Response::redirect($this->scopeUrl($selection['type'], $selection['key']));
    }

    private function isEnabled(mixed $value): bool
    {
        return $value === true || in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }

    private function currentAdminUser(): \Zoosper\Auth\Model\AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }
        return $user;
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
    private function writeScope(string $type, string $key): array
    {
        return match ($type) {
            'default' => [ScopeType::Default, null],
            'website' => [ScopeType::Website, $key],
            'store' => [ScopeType::Store, $key],
            'site' => [ScopeType::Site, $key],
            default => throw new InvalidArgumentException("Unsupported settings scope: {$type}"),
        };
    }

    private function scopeUrl(string $type, string $key): string
    {
        return $type === 'default'
            ? '/admin/settings'
            : '/admin/settings?scope=' . rawurlencode($type) . '&scope_key=' . rawurlencode($key);
    }
}
