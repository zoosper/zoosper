<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues\Admin;
use Throwable;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\GithubIssues\GithubIssueDataSourceFactory;
use Zoosper\Grid\DataSource\GridQuery;
final readonly class GithubIssueAdminController
{
    /** @param array<string,mixed> $config */
    public function __construct(private SessionGuard $guard, private AdminLayoutRendererInterface $layout,
        private GithubIssueDataSourceFactory $dataSources, private array $config, private AdminUrlGenerator $adminUrls) {}
    public function index(Request $request): Response
    {
        $user=$this->guard->user(); if($user===null)return Response::redirect($this->adminUrls->url('login'));
        try {
            $owner=(string)($this->config['owner']??'');$repository=(string)($this->config['repository']??'');
            $query=$request->queryParams();$size=in_array((int)($query['page_size']??20),[10,20,50],true)?(int)$query['page_size']:20;
            $cursor=is_string($query['cursor']??null)?$query['cursor']:null;
            $result=$this->dataSources->create($owner,$repository,$user->id)->fetch(new GridQuery(pageSize:$size,cursor:$cursor));
            $content='<p class="grid-summary">Showing '.count($result->items).' current remote result(s).</p>'.$this->table($result->items).$this->navigation($result->previousCursor,$result->nextCursor,$size);
            return Response::html($this->layout->render('GitHub Issues',$content,$user,'github-issues'));
        } catch (\InvalidArgumentException $exception) {
            return Response::html($this->layout->render('GitHub Issues','<div class="admin-alert admin-alert--error" role="alert">'.$this->e($exception->getMessage()).'</div>',$user,'github-issues'),422);
        } catch (Throwable) {
            return Response::html($this->layout->render('GitHub Issues','<div class="admin-alert admin-alert--error" role="alert">The GitHub Issues service is currently unavailable. No empty-result state has been shown.</div>',$user,'github-issues'),503);
        }
    }
    /** @param list<array<string,mixed>> $rows */
    private function table(array $rows): string
    {
        $body='';foreach($rows as $row){$body.='<tr><td>'.$this->e((string)$row['number']).'</td><td>'.$this->e((string)$row['title']).'</td><td>'.$this->e((string)$row['kind']).'</td><td>'.$this->e((string)$row['state']).'</td><td>'.$this->e((string)$row['labels']).'</td><td>'.$this->e((string)$row['updated_at']).'</td></tr>';}
        if($body==='')$body='<tr><td colspan="6" class="grid-empty">No GitHub issues were returned.</td></tr>';
        return '<table class="grid-table"><thead><tr><th>#</th><th>Title</th><th>Kind</th><th>State</th><th>Labels</th><th>Updated</th></tr></thead><tbody>'.$body.'</tbody></table>';
    }
    private function navigation(?string $previous,?string $next,int $pageSize): string
    {
        $path=$this->adminUrls->url('github-issues');$html='<nav class="grid-pagination" aria-label="GitHub issue cursor navigation">';
        $html.=$previous!==null?'<a rel="prev" href="'.$this->e($path.'?'.http_build_query(['page_size'=>$pageSize,'cursor'=>$previous],'','&',PHP_QUERY_RFC3986)).'">Previous</a>':'<span class="grid-pagination__disabled">Previous</span>';
        $html.=$next!==null?'<a rel="next" href="'.$this->e($path.'?'.http_build_query(['page_size'=>$pageSize,'cursor'=>$next],'','&',PHP_QUERY_RFC3986)).'">Next</a>':'<span class="grid-pagination__disabled">Next</span>';
        return $html.'</nav>';
    }
    private function e(string $value): string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
}
