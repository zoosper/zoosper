<?php

declare(strict_types=1);

return [
    /*
     * Template engine configuration.
     *
     * Phase 0.60 enables Latte as the recommended modern template engine while
     * keeping PHP templates available as a compatibility fallback. Developers
     * can still override the TemplateEngineRegistry from a module-owned
     * config/services.php file to add Twig or any custom engine later.
     */
    'default_engine' => 'latte',
    'preferred_modern_engine' => 'latte',
    'allow_custom_engines' => true,
    'php_templates_enabled' => true,
    'template_cache_path' => 'var/cache/templates',
];
