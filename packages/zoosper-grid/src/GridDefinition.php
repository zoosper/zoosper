<?php
declare(strict_types=1);
namespace Zoosper\Grid;
final readonly class GridDefinition
{
    /** @param list<GridColumn> $columns @param list<GridFilter> $filters */
    public function __construct(
        public string $title,
        public array $columns,
        public array $filters = [],
        public ?string $defaultSort = null,
        public string $defaultSortDir = 'desc',
        public string $emptyMessage = 'No records found.',
    ) {}
    /** @return list<string> */
    public function sortableColumnKeys(): array { $keys=[]; foreach($this->columns as $c){ if($c->sortable){$keys[]=$c->key;}} return $keys; }
    public function isSortable(string $key): bool { return in_array($key, $this->sortableColumnKeys(), true); }
    /** @return list<string> */
    public function filterKeys(): array { return array_map(static fn(GridFilter $f): string => $f->key, $this->filters); }
    /** @return list<string> */
    public function allColumnKeys(): array { return array_map(static fn(GridColumn $c): string => $c->key, $this->columns); }
    /** @return list<string> */
    public function toggleableColumnKeys(): array { $keys=[]; foreach($this->columns as $c){ if($c->toggleable){$keys[]=$c->key;}} return $keys; }
    /** @return list<string> */
    public function defaultVisibleColumnKeys(): array { $keys=[]; foreach($this->columns as $c){ if($c->defaultVisible){$keys[]=$c->key;}} return $keys; }
    /** @param list<GridColumn> $columns */
    public function withAdditionalColumns(array $columns): self { $existing=$this->allColumnKeys(); $merged=$this->columns; foreach($columns as $c){ if(!in_array($c->key,$existing,true)){ $merged[]=$c; $existing[]=$c->key; }} return new self($this->title,$merged,$this->filters,$this->defaultSort,$this->defaultSortDir,$this->emptyMessage); }
    /** @param list<GridFilter> $filters */
    public function withAdditionalFilters(array $filters): self { $existing=$this->filterKeys(); $merged=$this->filters; foreach($filters as $f){ if(!in_array($f->key,$existing,true)){ $merged[]=$f; $existing[]=$f->key; }} return new self($this->title,$this->columns,$merged,$this->defaultSort,$this->defaultSortDir,$this->emptyMessage); }
    /** @param list<string> $visibleKeys */
    public function withVisibleColumnKeys(array $visibleKeys): self { $filtered=array_values(array_filter($this->columns, static fn(GridColumn $c): bool => !$c->toggleable || in_array($c->key,$visibleKeys,true))); if($filtered===[]){$filtered=$this->columns;} return new self($this->title,$filtered,$this->filters,$this->defaultSort,$this->defaultSortDir,$this->emptyMessage); }
}












