<?php
declare(strict_types=1);
namespace Zoosper\AdminGrid\Tests\Unit;
use Zoosper\AdminGrid\{GridCompactWorkspaceRenderer,GridViewState};
use Zoosper\Pagination\Pager;
use Zoosper\Grid\{GridColumn,GridCriteria,GridDefinition,GridFilter};
function featureUrlState(): GridViewState{return new GridViewState(new GridDefinition('Media',[new GridColumn('id','ID',toggleable:false),new GridColumn('actions','Actions',toggleable:false)],[new GridFilter('q','Filename')]),new GridCriteria(new Pager(1,20),null,'desc',[]),['id','actions'],['id','actions'],[]);}
it('derives Clear all from the feature action and omits disabled export',function():void{$html=(new GridCompactWorkspaceRenderer())->render(featureUrlState(),'/admin/media',null,false);expect($html)->toContain('action="/admin/media"')->toContain('href="/admin/media">Clear all</a>')->not->toContain('data-grid-export')->not->toContain('/admin/pages');});
it('derives the Pages export from the feature action',function():void{$html=(new GridCompactWorkspaceRenderer())->render(featureUrlState(),'/admin/pages');expect($html)->toContain('href="/admin/pages">Clear all</a>')->toContain('href="/admin/pages/export"')->toContain('data-grid-export');});











