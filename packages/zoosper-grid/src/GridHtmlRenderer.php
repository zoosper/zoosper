<?php

declare(strict_types=1);

namespace Zoosper\Grid;

use Zoosper\Core\Pagination\PaginationResult;

final class GridHtmlRenderer
{
    /** @param PaginationResult<array<string,mixed>> $result */
    public function render(GridDefinition $definition, PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        return '<div class="grid">'.$this->renderFilterBar($definition,$criteria,$baseUrl)
            .$this->renderBody($definition,$result,$criteria,$baseUrl).'</div>';
    }

    /** Compact-workspace entry point: no legacy filter bar. @param PaginationResult<array<string,mixed>> $result */
    public function renderBody(GridDefinition $definition, PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        return $this->renderSummary($result).$this->renderTable($definition,$result,$criteria,$baseUrl)
            .$this->renderPagination($result,$criteria,$baseUrl);
    }

    private function renderFilterBar(GridDefinition $definition, GridCriteria $criteria, string $baseUrl): string
    {
        if ($definition->filters===[]) return '';
        $fields='';
        foreach ($definition->filters as $filter) {
            $current=$criteria->filters[$filter->key]??'';
            $fields.=$this->renderFilterField($filter,$current);
        }
        $hidden='';
        if ($criteria->sortBy!==null) {
            $hidden.='<input type="hidden" name="sort" value="'.$this->e($criteria->sortBy).'">';
            $hidden.='<input type="hidden" name="dir" value="'.$this->e($criteria->sortDir).'">';
        }
        $hidden.='<input type="hidden" name="page_size" value="'.(int)$criteria->pager->pageSize.'">';
        return '<form method="get" action="'.$this->e($baseUrl).'" class="grid-filters">'.$fields.$hidden
            .'<button type="submit" class="grid-filters__apply">Filter</button>'
            .'<a href="'.$this->e($baseUrl).'" class="grid-filters__reset">Reset</a></form>';
    }

    private function renderFilterField(GridFilter $filter, mixed $current): string
    {
        $name=$this->e($filter->key); $label=$this->e($filter->label);
        if ($filter->type==='select') {
            $options='<option value="">'.$label.'</option>';
            foreach ($filter->normalisedOptions() as $option) {
                $options.='<option value="'.$this->e($option->value).'"'.($option->value===(string)$current?' selected':'').'>'.$this->e($option->label).'</option>';
            }
            return '<label class="grid-filters__field"><span>'.$label.'</span><select name="'.$name.'">'.$options.'</select></label>';
        }
        return '<label class="grid-filters__field"><span>'.$label.'</span><input type="text" name="'.$name.'" value="'.$this->e((string)$current).'" placeholder="'.$label.'"></label>';
    }

    /** @param PaginationResult<array<string,mixed>> $result */
    private function renderSummary(PaginationResult $result): string
    {
        if ($result->total===0) return '';
        $from=($result->page-1)*$result->pageSize+1; $to=min($result->total,$result->page*$result->pageSize);
        return '<p class="grid-summary">Showing '.$from.'&ndash;'.$to.' of '.$result->total.'</p>';
    }

    /** @param PaginationResult<array<string,mixed>> $result */
    private function renderTable(GridDefinition $definition, PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        $head=''; foreach($definition->columns as $column){$head.=$this->renderHeaderCell($column,$criteria,$baseUrl);} $body='';
        if($result->items===[]){$body='<tr><td colspan="'.count($definition->columns).'" class="grid-empty">'.$this->e($definition->emptyMessage).'</td></tr>';}
        else foreach($result->items as $row){$body.='<tr>';foreach($definition->columns as $column){$value=$row[$column->key]??null;$align=$column->align!=='left'?' style="text-align:'.$this->e($column->align).'"':'';$body.='<td data-grid-column="'.$this->e($column->key).'"'.$align.'>'.$column->renderValue($value,$row).'</td>';}$body.='</tr>';}
        return '<table class="grid-table"><thead><tr>'.$head.'</tr></thead><tbody>'.$body.'</tbody></table>';
    }

    private function renderHeaderCell(GridColumn $column, GridCriteria $criteria, string $baseUrl): string
    {
        $label=$this->e($column->label); if(!$column->sortable)return '<th>'.$label.'</th>';
        $active=$criteria->sortBy===$column->key;$params=array_merge($this->linkParameters($criteria),['sort'=>$column->key,'dir'=>$criteria->toggledSortDir($column->key)]);unset($params['page']);
        $indicator=$active?($criteria->sortDir==='asc'?' ▲':' ▼'):'';
        return '<th data-grid-column="' . $this->e($column->key) . '" class="grid-sortable'.($active?' grid-sort--active':'').'"><a href="'.$this->e($this->url($baseUrl,$params)).'">'.$label.$indicator.'</a></th>';
    }

    /** @param PaginationResult<array<string,mixed>> $result */
    private function renderPagination(PaginationResult $result, GridCriteria $criteria, string $baseUrl): string
    {
        if($result->total===0)return '';$base=$this->linkParameters($criteria);
        $prev=$result->hasPrevious()?'<a href="'.$this->e($this->url($baseUrl,array_merge($base,['page'=>$result->page-1]))).'" class="grid-pagination__prev">&laquo; Previous</a>':'<span class="grid-pagination__prev grid-pagination__disabled">&laquo; Previous</span>';
        $next=$result->hasNext()?'<a href="'.$this->e($this->url($baseUrl,array_merge($base,['page'=>$result->page+1]))).'" class="grid-pagination__next">Next &raquo;</a>':'<span class="grid-pagination__next grid-pagination__disabled">Next &raquo;</span>';
        return '<nav class="grid-pagination">'.$prev.'<span class="grid-pagination__status">Page '.$result->page.' of '.$result->totalPages().'</span>'.$next.'</nav>';
    }

    /** @return array<string, mixed> */
    private function linkParameters(GridCriteria $criteria): array
    {
        $parameters = [];
        foreach ($criteria->filters as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            $parameters[$key] = $this->normaliseQueryValue($value);
        }
        $parameters['page_size'] = $criteria->pager->pageSize;
        if ($criteria->pager->page > 1) {
            $parameters['page'] = $criteria->pager->page;
        }
        if ($criteria->sortBy !== null) {
            $parameters['sort'] = $criteria->sortBy;
            $parameters['dir'] = $criteria->sortDir;
        }
        return $parameters;
    }

    private function normaliseQueryValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $result = [];
        array_walk_recursive($value, static function (mixed $item) use (&$result): void {
            if ((string) $item !== '') {
                $result[] = (string) $item;
            }
        });
        return array_values(array_unique($result));
    }

    /** @param array<string,mixed> $params */
    private function url(string $baseUrl,array $params):string{$filtered=array_filter($params,static fn(mixed $v):bool=>$v!==null&&$v!=='');$q=http_build_query($filtered);return $q!==''?$baseUrl.'?'.$q:$baseUrl;}
    private function e(string $value):string{return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
}
