<?php

declare(strict_types=1);

namespace Zoosper\Theme\Theme;

final readonly class Theme
{
    public function __construct(public string $code, public string $path)
    {
    }

    public function templatePath(string $template): string
    {
        return rtrim($this->path, '/') . '/templates/' . ltrim($template, '/');
    }
}
