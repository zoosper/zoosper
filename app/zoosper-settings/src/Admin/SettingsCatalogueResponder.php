<?php

declare(strict_types=1);

namespace Zoosper\Settings\Admin;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Settings\Value\ScopedSettingValueResolver;

/** Owns Settings catalogue query resolution and Admin view composition. */
final readonly class SettingsCatalogueResponder
{
    public function __construct(
        private AdminViewRendererInterface $views,
        private ModuleSettingsCatalogueLoader $catalogue,
        private ScopedSettingValueResolver $values,
        private SettingsScopeSelection $scopeSelection,
        private CsrfTokenManager $csrf,
        private SettingsAdminUrls $urls,
    ) {
    }

    public function respond(Request $request, AdminUser $user): Response
    {
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
                'showPaths' => self::isEnabled($effectiveValues['settings.catalogue.show_paths']->value ?? true),
                'indexUrl' => $this->urls->url('settings'),
                'saveUrl' => $this->urls->url('settings/save'),
                'clearUrl' => $this->urls->url('settings/clear'),
            ],
            user: $user,
            active: 'settings',
        ));
    }

    private static function isEnabled(mixed $value): bool
    {
        return $value === true || in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
