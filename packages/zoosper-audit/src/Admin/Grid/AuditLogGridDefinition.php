<?php
declare(strict_types=1);
namespace Zoosper\Audit\Admin\Grid;
use Zoosper\Audit\Admin\AuditLogGrid;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
final readonly class AuditLogGridDefinition
{
    public const KEY='admin.audit-log';
    public function __construct(private ?GridColumnRegistry $columns=null){}
    public function build():GridDefinition{return $this->columns?->apply('audit-log',AuditLogGrid::definition())??AuditLogGrid::definition();}
}









