<?php
declare(strict_types=1);
use Zoosper\Logger\Manager\LogManager;
it('redacts credential keys and PAT-shaped values regardless of key name',function():void{$root=sys_get_temp_dir().'/zl-'.bin2hex(random_bytes(4));$c=new class{public function get(string $k,mixed $d=null):mixed{return ['logging.path'=>'log'][$k]??$d;}};$pat='zp_pat_'.str_repeat('a',16).'_'.str_repeat('b',64);(new LogManager($c,$root))->forFile('security.log')->info('probe',['authorization'=>'Bearer '.$pat,'header'=>'Bearer '.$pat,'cookie'=>'x']);$f=$root.'/log/security-'.date('Y-m-d').'.log';$v=file_get_contents($f);expect($v)->not->toContain($pat)->not->toContain('Bearer zp_pat_')->toContain('[redacted]');exec('rm -rf '.escapeshellarg($root));});
