<?php
declare(strict_types=1);
use Zoosper\UrlRewrite\Api\UrlRewriteApiController;
return [
 ['method'=>'GET','path'=>'/api/v1/url-rewrites','controller'=>UrlRewriteApiController::class,'action'=>'index','public'=>true,'stateless'=>true],
 ['method'=>'GET','path'=>'/api/v1/url-rewrites/{id:\d+}','controller'=>UrlRewriteApiController::class,'action'=>'show','public'=>true,'stateless'=>true],
 ['method'=>'POST','path'=>'/api/v1/url-rewrites','controller'=>UrlRewriteApiController::class,'action'=>'create','public'=>true,'stateless'=>true],
 ['method'=>'PATCH','path'=>'/api/v1/url-rewrites/{id:\d+}','controller'=>UrlRewriteApiController::class,'action'=>'update','public'=>true,'stateless'=>true],
 ['method'=>'POST','path'=>'/api/v1/url-rewrites/{id:\d+}/disable','controller'=>UrlRewriteApiController::class,'action'=>'disable','public'=>true,'stateless'=>true],
 ['method'=>'POST','path'=>'/api/v1/url-rewrites/{id:\d+}/restore','controller'=>UrlRewriteApiController::class,'action'=>'restore','public'=>true,'stateless'=>true],
 ['method'=>'DELETE','path'=>'/api/v1/url-rewrites/{id:\d+}','controller'=>UrlRewriteApiController::class,'action'=>'deletePermanently','public'=>true,'stateless'=>true],
];
