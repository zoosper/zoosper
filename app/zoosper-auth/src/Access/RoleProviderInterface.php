<?php

declare(strict_types=1);

namespace Zoosper\Auth\Access;

interface RoleProviderInterface
{
    public function get(string $code): ?Role;
}
