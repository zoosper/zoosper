<?php
declare(strict_types=1);
namespace Zoosper\Admin\Audit\Grid;
use Zoosper\Admin\Audit\LoginHistoryGrid;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
final readonly class LoginHistoryGridDefinition
{
    public const KEY='admin.login-history';
    public function __construct(private ?GridColumnRegistry $columns=null){}
    public function build():GridDefinition{return $this->columns?->apply('login-history',LoginHistoryGrid::definition())??LoginHistoryGrid::definition();}
}
