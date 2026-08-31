<?php
declare(strict_types=1);
namespace Zoosper\UrlRewrite\Lifecycle;
final readonly class UrlRewriteLifecycleResult{public function __construct(public bool $successful,public string $operation,public int $urlRewriteId,public ?bool $active=null,public array $blockers=[],public ?string $message=null){}}










