<?php
declare(strict_types=1);
namespace Zoosper\Admin\Audit\Grid;
use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridHtmlRenderer;
final readonly class OperationalGridPageBuilderFactory
{
    public function __construct(private GridViewStateResolver $stateResolver,private ?GridColumnOrderer $columnOrderer=null,private ?GridCompactWorkspaceRenderer $workspaceRenderer=null,private ?GridHtmlRenderer $gridRenderer=null){}
    public function create():OperationalGridPageBuilder{return new OperationalGridPageBuilder($this->workspace(),$this->gridRenderer??new GridHtmlRenderer());}
    private function workspace(): OperationalGridWorkspace
    {
        return new OperationalGridWorkspace(
            resolver: $this->stateResolver,
            renderer: $this->workspaceRenderer ?? new GridCompactWorkspaceRenderer(),
            columnOrderer: $this->columnOrderer,
        );
    }
}
