<?php
declare(strict_types=1);
namespace Zoosper\Media\Admin\Grid;
use Zoosper\AdminGrid\{AdminCollectionGridQuery,GridCompactWorkspaceRenderer,GridViewState,GridViewStateResolver};use Zoosper\Core\Http\Request;use Zoosper\Grid\GridColumnOrderer;
final readonly class MediaVisualGridWorkspace
{
 public function __construct(private GridViewStateResolver $states,private GridCompactWorkspaceRenderer $controls,private GridColumnOrderer $orderer,private MediaGridSource $source,private MediaVisualGridRenderer $cards){}
 public function render(int $adminUserId,Request $request,string $action,string $csrf):string{$definition=$this->source->definition();$state=$this->states->resolve($adminUserId,MediaGridSource::KEY,$definition,AdminCollectionGridQuery::values($request,$definition),AdminCollectionGridQuery::bookmark($request));$controlState=new GridViewState($this->orderer->apply($definition,$state->columnOrder),$state->criteria,$state->visibleColumns,$state->columnOrder,$state->bookmarks,$state->activeBookmarkId);$page=$this->source->paginate($state->criteria);return $this->controls->render($controlState,$action,null,false).$this->cards->render($page,$state->criteria,$state->visibleColumns,$csrf);}
}
