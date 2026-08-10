<?php
declare(strict_types=1);
namespace Zoosper\Menu\Model;
final readonly class Menu { public function __construct(public int $id,public int $siteId,public string $code,public string $label,public string $status,public string $createdAt,public string $updatedAt){} public function isActive(): bool{return $this->status==='active';} }
