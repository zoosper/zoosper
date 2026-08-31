<?php
declare(strict_types=1);
namespace Zoosper\AdminGrid;
use Zoosper\Grid\{GridColumnOrderer,GridDataSourceInterface,GridDefinition,GridHtmlRenderer};
final readonly class AdminCollectionGrid{public function __construct(private GridViewStateResolver $resolver,private GridCompactWorkspaceRenderer $workspace=new GridCompactWorkspaceRenderer(),private GridHtmlRenderer $table=new GridHtmlRenderer(),private GridColumnOrderer $orderer=new GridColumnOrderer()){} /** @param array<string,mixed> $query @return array{html:string,state:GridViewState} */ public function render(int $userId,string $key,string $action,GridDefinition $definition,GridDataSourceInterface $source,array $query,?int $bookmark=null):array{$state=$this->resolver->resolve($userId,$key,$definition,$query,$bookmark);$controls=new GridViewState($this->orderer->apply($definition,$state->columnOrder),$state->criteria,$state->visibleColumns,$state->columnOrder,$state->bookmarks,$state->activeBookmarkId);$page=$source->paginate($state->criteria);return['html'=>$this->workspace->render($controls,$action).$this->table->renderBody($state->definition,$page,$state->criteria,$action),'state'=>$state];}}











