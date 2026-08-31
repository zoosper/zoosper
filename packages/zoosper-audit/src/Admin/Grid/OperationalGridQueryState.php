<?php
declare(strict_types=1);
namespace Zoosper\Audit\Admin\Grid;
use Zoosper\Core\Http\Request;
use Zoosper\Grid\GridDefinition;
final class OperationalGridQueryState
{
    /** @return array<string,mixed> */
    public static function fromRequest(Request $request,GridDefinition $definition):array
    {
        $out=[];
        foreach(array_merge(['page','page_size','sort','dir','bookmark','visible_columns','column_order'],$definition->filterKeys()) as $key){
            $value=$request->query($key);
            if($value!==null)$out[$key]=$value;
        }
        return $out;
    }
    public static function bookmarkId(Request $request):?int
    {
        $raw=$request->query('bookmark');
        return is_string($raw)&&preg_match('/^[1-9][0-9]*$/',$raw)===1?(int)$raw:null;
    }
    private function __construct(){}
}









