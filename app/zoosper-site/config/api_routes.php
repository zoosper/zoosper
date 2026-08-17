<?php
declare(strict_types=1);
use Zoosper\Site\Api\SiteApiController;
return [
 ['method'=>'POST','path'=>'/api/v1/sites','controller'=>SiteApiController::class,'action'=>'create','public'=>true,'stateless'=>true],
 ['method'=>'PATCH','path'=>'/api/v1/sites/{id:\d+}','controller'=>SiteApiController::class,'action'=>'update','public'=>true,'stateless'=>true],
 ['method'=>'GET','path'=>'/api/v1/sites','controller'=>SiteApiController::class,'action'=>'index','public'=>true,'stateless'=>true],
 ['method'=>'GET','path'=>'/api/v1/sites/{id:\d+}','controller'=>SiteApiController::class,'action'=>'show','public'=>true,'stateless'=>true],
 ['method'=>'POST','path'=>'/api/v1/sites/{id:\d+}/disable','controller'=>SiteApiController::class,'action'=>'disable','public'=>true,'stateless'=>true],
 ['method'=>'POST','path'=>'/api/v1/sites/{id:\d+}/restore','controller'=>SiteApiController::class,'action'=>'restore','public'=>true,'stateless'=>true],
 ['method'=>'DELETE','path'=>'/api/v1/sites/{id:\d+}','controller'=>SiteApiController::class,'action'=>'deletePermanently','public'=>true,'stateless'=>true],
];
