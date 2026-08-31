<?php
declare(strict_types=1);
use Zoosper\Theme\Api\ThemeApiController;
return [
 ['method'=>'GET','path'=>'/api/v1/themes','controller'=>ThemeApiController::class,'action'=>'index','public'=>true,'stateless'=>true],
 ['method'=>'POST','path'=>'/api/v1/sites/{id:\d+}/theme','controller'=>ThemeApiController::class,'action'=>'assign','public'=>true,'stateless'=>true],
];










