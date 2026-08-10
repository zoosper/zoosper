<?php
declare(strict_types=1);
use Zoosper\Menu\Api\MenuController;
return [['method'=>'GET','path'=>'/api/v1/menu','controller'=>MenuController::class,'action'=>'show','public'=>true]];
