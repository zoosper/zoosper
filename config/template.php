<?php

declare(strict_types=1);

return [
    /*
     * Template engine configuration.
     *
     * Phase 0.59 introduces an adapter layer and keeps PHP templates as the
     * compatibility engine. Latte is the recommended first modern engine for a
     * future phase, but the adapter design lets developers replace the engine
     * through module-owned config/services.php without changing core bootstrap.
     */
    'default_engine' => 'php',
    'preferred_modern_engine' => 'latte',
    'allow_custom_engines' => true,
    'php_templates_enabled' => true,
    'template_cache_path' => 'var/cache/templates',
];
