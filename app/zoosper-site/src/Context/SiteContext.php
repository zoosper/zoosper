<?php
declare(strict_types=1);

namespace Zoosper\Site\Context;
final readonly class SiteContext
{
    public function __construct(public string $code, public string $name, public string $host)
    {
    }
}
