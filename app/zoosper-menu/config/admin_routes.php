<?php
declare(strict_types=1);
use Zoosper\Menu\Admin\Controller\MenuAdminController;
return [
 ['method'=>'GET','path'=>'/admin/menus','controller'=>MenuAdminController::class,'action'=>'index','permission'=>'menu.manage'],
 ['method'=>'GET','path'=>'/admin/menus/create','controller'=>MenuAdminController::class,'action'=>'create','permission'=>'menu.manage'],
 ['method'=>'POST','path'=>'/admin/menus','controller'=>MenuAdminController::class,'action'=>'store','permission'=>'menu.manage'],
 ['method'=>'GET','path'=>'/admin/menus/{id:\\d+}/edit','controller'=>MenuAdminController::class,'action'=>'edit','permission'=>'menu.manage'],
 ['method'=>'POST','path'=>'/admin/menus/{id:\\d+}/edit','controller'=>MenuAdminController::class,'action'=>'update','permission'=>'menu.manage'],
 ['method'=>'POST','path'=>'/admin/menus/{id:\\d+}/items','controller'=>MenuAdminController::class,'action'=>'addItem','permission'=>'menu.manage'],
 ['method'=>'POST','path'=>'/admin/menus/{id:\\d+}/delete','controller'=>MenuAdminController::class,'action'=>'delete','permission'=>'menu.manage'],
];
