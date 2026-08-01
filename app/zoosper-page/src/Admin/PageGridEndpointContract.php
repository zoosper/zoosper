<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/** Fixed paths and method policy for the Pages Grid Workspace. */
final class PageGridEndpointContract
{
    public const VIEW_METHOD = 'GET';
    public const VIEW_PATH = '/admin/pages';
    public const MUTATION_METHOD = 'POST';
    public const MUTATION_PATH = '/admin/pages/grid';

    private function __construct()
    {
    }
}
