<?php

declare(strict_types=1);

return [
    'required_roots' => [
        'app',
        'assets',
        'bin',
        'bootstrap',
        'config',
        'database',
        'deploy',
        'docs',
        'modules',
        'public',
        'storage',
        'tests',
        'themes',
        'tools',
        'var',
    ],
    'optional_roots' => [
        'vendor',
        'node_modules',
    ],
    'forbidden_public_roots' => [
        'var',
        'storage',
        'vendor',
        'node_modules',
        'app',
        'config',
        'modules',
        'themes',
        'tools',
        'database',
        'deploy',
        'docs',
        'tests',
        'private',
        'tmp',
        'cache',
        'logs',
    ],
    'root_file_policy' => [
        'allowed_build_files' => [
            'package.json',
            'package-lock.json',
            'vite.admin-editor.config.js',
        ],
        'allowed_project_files' => [
            'README.md',
            'AGENTS.md',
            'CLAUDE.md',
            'composer.json',
            'composer.lock',
            '.env',
            '.env.example',
            '.gitignore',
        ],
    ],
];
