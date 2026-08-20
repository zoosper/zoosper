<?php
declare(strict_types=1);
namespace Zoosper\AdminGrid;
use Zoosper\Core\Http\Request;use Zoosper\Grid\GridDefinition;
final class AdminCollectionGridQuery{/** @return array<string,mixed> */public static function values(Request $r,GridDefinition $d):array{$o=[];foreach(array_merge(['page','page_size','sort','dir','bookmark','visible_columns','column_order'],$d->filterKeys()) as $k){$v=$r->query($k);if($v!==null)$o[$k]=$v;}return$o;}public static function bookmark(Request $r):?int{$v=$r->query('bookmark');return is_string($v)&&preg_match('/^[1-9][0-9]*$/',$v)===1?(int)$v:null;}private function __construct(){}}
