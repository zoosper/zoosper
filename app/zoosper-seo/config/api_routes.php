<?php
declare(strict_types=1);
use Zoosper\Seo\Controller\SeoPublicController;
return [['method'=>'GET','path'=>'/sitemap.xml','controller'=>SeoPublicController::class,'action'=>'sitemap','public'=>true],['method'=>'GET','path'=>'/robots.txt','controller'=>SeoPublicController::class,'action'=>'robots','public'=>true]];
