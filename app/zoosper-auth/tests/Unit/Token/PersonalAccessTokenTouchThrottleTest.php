<?php
declare(strict_types=1);
it('updates PAT last-used metadata only outside a five-minute database interval',function():void{$root=dirname(__DIR__,3);$repo=file_get_contents($root.'/src/Token/PersonalAccessTokenRepository.php');$auth=file_get_contents($root.'/src/Token/PersonalAccessTokenAuthenticator.php');expect($repo)->toContain('last_used_at IS NULL OR last_used_at<:cutoff')->toContain("'cutoff'=>\$cutoff")->and($auth)->toContain('$now-300');});










