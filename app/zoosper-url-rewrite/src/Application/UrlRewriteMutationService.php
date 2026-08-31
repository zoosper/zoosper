<?php
declare(strict_types=1);
namespace Zoosper\UrlRewrite\Application;
use Zoosper\Audit\Contract\AuditLoggerInterface;use Zoosper\UrlRewrite\Model\UrlRewrite;use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;use Zoosper\UrlRewrite\Service\{RedirectChainInspector,RedirectPolicy};
final readonly class UrlRewriteMutationService{public function __construct(private UrlRewriteRepository $rewrites,private RedirectPolicy $policy,private RedirectChainInspector $chains,private ?AuditLoggerInterface $audit=null){}public function save(int $siteId,?UrlRewrite $existing,array $input,int $actorId,string $actorEmail):UrlRewrite{$v=$this->policy->validate((string)($input['request_path']??$existing?->requestPath??''),(string)($input['target_path']??$existing?->targetPath??''),(int)($input['redirect_type']??$existing?->redirectType??301));$this->chains->inspect($siteId,$v['source'],$v['target']);$id=$this->rewrites->save($existing?->id,$siteId,$v['source'],$v['target'],$v['type'],(bool)($input['active']??$existing?->isActive??true));$saved=$this->rewrites->findByIdForSite($id,$siteId);if($saved===null)throw new \RuntimeException('Saved URL Rewrite could not be reloaded.');$action=$existing===null?'url_rewrite.created':'url_rewrite.updated';$this->audit?->logAction($actorId,$actorEmail,$action,'url_rewrite',(string)$id,$action,['site_id'=>$siteId,'request_path'=>$saved->requestPath,'target_path'=>$saved->targetPath,'redirect_type'=>$saved->redirectType]);return$saved;}}










