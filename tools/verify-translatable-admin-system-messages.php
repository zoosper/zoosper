<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper translatable admin system message verification\n";
print "======================================================\n\n";

$identityTranslator = new \Zoosper\Core\I18n\IdentityTranslator();
$translationResolver = class_exists(\Zoosper\Core\I18n\TranslationResolver::class)
    ? new \Zoosper\Core\I18n\TranslationResolver($basePath)
    : null;
$resolvedTranslator = $translationResolver?->forLocale('en_AU', 'en_AU');

$usesIdentityFallback = str_contains($controller, 'IdentityTranslator');
$usesCatalogueResolver = str_contains($controller, 'TranslationResolver')
    && str_contains($controller, 'defaultTranslator')
    && str_contains($controller, '$this->defaultTranslator()');

$checks = [
    'TranslatorInterface exists' => interface_exists(\Zoosper\Core\I18n\TranslatorInterface::class),
    'IdentityTranslator exists' => class_exists(\Zoosper\Core\I18n\IdentityTranslator::class),
    'IdentityTranslator implements TranslatorInterface' => $identityTranslator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'IdentityTranslator returns original message' => $identityTranslator->translate('Page saved successfully.') === 'Page saved successfully.',
    'IdentityTranslator replaces placeholders' => $identityTranslator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
    'TranslationResolver exists when catalogue-backed resolution is enabled' => class_exists(\Zoosper\Core\I18n\TranslationResolver::class),
    'TranslationResolver returns TranslatorInterface' => $resolvedTranslator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'TranslationResolver resolves catalogue message' => $resolvedTranslator?->translate('Page saved successfully.') === 'Page saved successfully.',
    'PageAdminController still imports TranslatorInterface' => str_contains($controller, 'TranslatorInterface'),
    'PageAdminController uses supported fallback strategy' => $usesIdentityFallback || $usesCatalogueResolver,
    'PageAdminController uses catalogue resolver after Phase 0.91' => $usesCatalogueResolver,
    'PageAdminController no longer needs direct IdentityTranslator import after Phase 0.91' => !str_contains($controller, 'use Zoosper\\Core\\I18n\\IdentityTranslator;'),
    'PageAdminController accepts translator dependency' => str_contains($controller, '?TranslatorInterface $translator'),
    'PageAdminController has t helper' => str_contains($controller, 'private function t(string $message'),
    'CSRF flash message is translated' => str_contains($controller, 'error($this->t(\'Unable to save page. Invalid security token.\')'),
    'create success message is translated' => str_contains($controller, 'success($this->t(\'Page created successfully.\')'),
    'save success message is translated' => str_contains($controller, 'success($this->t(\'Page saved successfully.\')'),
    'publish status messages are translated' => str_contains($controller, '? $this->t(\'Page published successfully.\')'),
    'invalid token page title is translated' => str_contains($controller, 'html($this->t(\'Invalid token\')'),
    'not found page title is translated' => str_contains($controller, 'html($this->t(\'Page not found\')'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
