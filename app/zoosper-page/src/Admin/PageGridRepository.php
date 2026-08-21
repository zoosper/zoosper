<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use PDO;
use Zoosper\Pagination\PaginationResult;

final readonly class PageGridRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return PaginationResult<array<string, mixed>> */
    public function paginate(PageGridCriteria $criteria): PaginationResult
    {
        [$where, $params] = $this->whereClause($criteria);
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM pages p ' . $where);
        $this->bind($count, $params); $count->execute();
        $total=(int)$count->fetchColumn();
        $sql='SELECT p.*, s.name AS site_name FROM pages p LEFT JOIN sites s ON s.id=p.site_id '
            .$where.' ORDER BY '.$this->orderBy($criteria).' LIMIT :limit OFFSET :offset';
        $statement=$this->pdo->prepare($sql); $this->bind($statement,$params);
        $statement->bindValue(':limit',$criteria->pager->pageSize,PDO::PARAM_INT);
        $statement->bindValue(':offset',$criteria->pager->offset(),PDO::PARAM_INT);
        $statement->execute();
        return new PaginationResult(items:$statement->fetchAll(),total:$total,page:$criteria->pager->page,pageSize:$criteria->pager->pageSize);
    }

    private function orderBy(PageGridCriteria $criteria): string
    {
        $columns=['id'=>'p.id','title'=>'p.title','slug'=>'p.slug','status'=>'p.status'];
        return ($columns[$criteria->sortBy??'']??'p.updated_at').' '.($criteria->sortDir==='asc'?'ASC':'DESC').', p.id DESC';
    }

    /** @return array{0:string,1:array<string,string|int>} */
    private function whereClause(PageGridCriteria $criteria): array
    {
        $conditions=[];$params=[];
        if($criteria->query!==''){$conditions[]='(p.title LIKE :query OR p.slug LIKE :query)';$params['query']='%'.$criteria->query.'%';}
        if($criteria->title!==''){$conditions[]='p.title LIKE :title';$params['title']='%'.$criteria->title.'%';}
        if($criteria->slug!==''){$conditions[]='p.slug LIKE :slug';$params['slug']='%'.$criteria->slug.'%';}
        if($criteria->status!==''){$conditions[]='p.status = :status';$params['status']=$criteria->status;}
        $siteIds=$criteria->siteIds!==[]?$criteria->siteIds:($criteria->siteId!==null?[$criteria->siteId]:[]);
        if($siteIds!==[]){$holders=[];foreach($siteIds as $i=>$id){$name='site_id_'.$i;$holders[]=':'.$name;$params[$name]=$id;}$conditions[]='p.site_id IN ('.implode(', ',$holders).')';}
        return [$conditions===[]?'':'WHERE '.implode(' AND ',$conditions),$params];
    }

    /** @param array<string,string|int> $params */
    private function bind(\PDOStatement $statement,array $params):void
    {foreach($params as $name=>$value){$statement->bindValue(':'.$name,$value,is_int($value)?PDO::PARAM_INT:PDO::PARAM_STR);}}
}
