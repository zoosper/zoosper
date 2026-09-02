<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterValue;

/** Compact, progressive-disclosure renderer for a resolved Grid state. */
final readonly class GridCompactWorkspaceRenderer
{
    public function __construct(
        private GridCompactToolbarRenderer $toolbar = new GridCompactToolbarRenderer(),
        private GridCompactFilterChipsRenderer $chips = new GridCompactFilterChipsRenderer(),
    ) {
    }

    /** @param list<int> $pageSizeOptions */
    public function render(
        GridViewState $state,
        string $formAction,
        ?string $exportUrl = null,
        bool $exportEnabled = true,
        array $pageSizeOptions = [20, 50, 100, 200],
    ): string
    {
        $filters=$state->criteria->filters;
        $active=0; foreach($filters as $value){if(is_array($value)?$value!==[]:trim((string)$value)!==''){$active++;}}
        $label='Default view';
        foreach($state->bookmarks as $bookmark){if((int)$bookmark['id']===$state->activeBookmarkId){$label=(string)$bookmark['name'];break;}}
        $html='<section data-grid-workspace data-grid-current-page-filename="'.$this->e($this->currentPageFilename($formAction)).'">';
        $html .= $this->toolbar->render(
            $label,
            false,
            $state->criteria->pager->pageSize,
            $active,
            $exportEnabled ? ($exportUrl ?? rtrim($formAction, '/') . '/export') : null,
            $state->bookmarks,
            $state->activeBookmarkId,
            $formAction,
            $pageSizeOptions,
        );
        $html.='<form method="get" action="'.$this->e($formAction).'" data-grid-filter-form>';
        $html.='<input type="hidden" name="page" value="1"><input type="hidden" name="columns_submitted" value="1">';
        $html.=$this->filters($state->definition,$filters,$formAction);
        $html.=$this->columns($state->definition,$state->visibleColumns,$state->columnOrder);
        if($state->criteria->sortBy!==null){$html.='<input type="hidden" name="sort" value="'.$this->e($state->criteria->sortBy).'"><input type="hidden" name="dir" value="'.$this->e($state->criteria->sortDir).'">';}
        $html.='</form>'.$this->chips->render($this->chipValues($state->definition,$filters)).'</section>';
        return $html;
    }

    /** @param array<string,mixed> $values */
    private function filters(GridDefinition $definition,array $values,string $clearAction):string
    {
        $html='<div id="grid-filters-panel" class="grid-compact-panel" hidden data-grid-panel="filters"'
            . ' aria-labelledby="grid-filters-title"><div class="grid-compact-panel__head">'
            . '<strong id="grid-filters-title">Filters</strong><button type="button" data-grid-panel-close'
            . ' aria-label="Close filters">×</button></div><div class="grid-compact-filters">';
        foreach ($definition->filters as $filter) {
            $dependencies = match ($filter->key) {
                'q' => '',
                'title' => 'title',
                'slug' => 'slug',
                'status' => 'status',
                'site_id' => 'site_name',
                default => $filter->key,
            };
            $html .= '<label data-grid-filter-columns="' . $this->e($dependencies) . '"><span>'
                . $this->e($filter->label) . '</span>'
                . $this->control($filter, $values[$filter->key] ?? null) . '</label>';
        }
        return $html . '<div class="grid-compact-panel__actions"><a href="' . $this->e($clearAction)
            . '">Clear all</a><button type="submit">Apply filters</button></div></div></div>';
    }

    private function control(GridFilter $filter,mixed $value):string
    {
        $key=$this->e($filter->key);
        if(in_array($filter->type,['text','date'],true))return '<input type="'.$filter->type.'" name="'.$key.'" value="'.$this->e((string)$value).'">';
        $multi=$filter->type==='multiselect';$selected=$multi?array_fill_keys(GridFilterValue::many($value),true):[(string)$value=>true];
        $html='<select name="'.$key.($multi?'[]" multiple':'"').'>';
        if(!$multi)$html.='<option value="">All</option>';
        foreach($filter->normalisedOptions() as $option){$html.='<option value="'.$this->e($option->value).'"'.(isset($selected[$option->value])?' selected':'').'>'.$this->e($option->label).'</option>';}
        return $html.'</select>';
    }

    /** @param list<string> $visible @param list<string> $order */
    private function columns(GridDefinition $definition,array $visible,array $order):string
    {
        $map=[];foreach($definition->columns as $column){$map[$column->key]=$column;}
        $html='<div id="grid-columns-panel" class="grid-compact-panel" hidden data-grid-panel="columns"'
            . ' aria-labelledby="grid-columns-title"><div class="grid-compact-panel__head">'
            . '<strong id="grid-columns-title">Columns</strong><button type="button" data-grid-panel-close'
            . ' aria-label="Close columns">×</button></div>'
            . '<div class="grid-compact-columns" data-grid-column-list>';
        foreach($order as $key){if(!isset($map[$key]))continue;$column=$map[$key];$html.='<label class="grid-compact-column" draggable="true" data-column-key="'.$this->e($key).'"><input type="checkbox" name="visible_columns[]" value="'.$this->e($key).'"'.(in_array($key,$visible,true)?' checked':'').(!$column->toggleable?' disabled':'').'> '.$this->e($column->label).'<input type="hidden" name="column_order[]" value="'.$this->e($key).'"></label>';}
        return $html . '</div><div class="grid-compact-panel__actions">'
            . '<button type="submit">Apply columns</button></div></div>';
    }

    /** @param array<string,mixed> $filters @return array<string,scalar|list<scalar>> */
    private function chipValues(GridDefinition $definition,array $filters):array
    {
        $out=[];foreach($definition->filters as $filter){$value=$filters[$filter->key]??null;if($value===null||$value===''||$value===[])continue;$labels=[];$options=[];foreach($filter->normalisedOptions() as $option){$options[$option->value]=$option->label;}foreach ($this->flatten($value) as $item) { $labels[] = $options[$item] ?? $item; }$out[$filter->key]=$labels;}
        return $out;
    }

    /** @return list<string> */
    private function flatten(mixed $value): array
    {
        if (!is_array($value)) {
            return (string) $value === '' ? [] : [(string) $value];
        }
        $result = [];
        array_walk_recursive($value, static function (mixed $item) use (&$result): void {
            if ((string) $item !== '') {
                $result[] = (string) $item;
            }
        });
        return array_values(array_unique($result));
    }

    private function currentPageFilename(string $formAction): string
    {
        $path = trim((string) parse_url($formAction, PHP_URL_PATH), '/');
        $segment = basename($path);
        $segment = preg_replace('/[^a-z0-9_-]+/i', '-', $segment) ?? '';
        $segment = strtolower(trim($segment, '-_'));

        return ($segment !== '' ? $segment : 'grid') . '-current-page.csv';
    }

    private function e(string $value):string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
}











