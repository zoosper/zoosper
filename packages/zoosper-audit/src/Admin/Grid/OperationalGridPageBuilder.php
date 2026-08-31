<?php
declare(strict_types=1);
namespace Zoosper\Audit\Admin\Grid;
use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridHtmlRenderer;
final readonly class OperationalGridPageBuilder
{
    public function __construct(private OperationalGridWorkspace $workspace,private GridHtmlRenderer $renderer){}
    /** @param array<string,mixed> $queryState */
    public function build(string $title,int $adminUserId,string $gridKey,string $action,GridDefinition $definition,GridDataSourceInterface $source,array $queryState,?int $bookmarkId=null):OperationalGridPage
    {
        $resolved=$this->workspace->resolve(adminUserId:$adminUserId,gridKey:$gridKey,action:$action,definition:$definition,queryState:$queryState,bookmarkId:$bookmarkId);
        $state=$resolved['state'];
        $pagination=$source->paginate($state->criteria);
        return new OperationalGridPage($title,$resolved['html'],$this->renderer->renderBody($state->definition,$pagination,$state->criteria,$action),$state,$pagination);
    }
}









