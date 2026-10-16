<?php
declare(strict_types=1);
it('locks associative PDO hydration at selected repository boundaries',function():void{$root=dirname(__DIR__,5);foreach(['app/zoosper-auth/src/Dashboard/DashboardRolePreferenceRepository.php','app/zoosper-page/src/Repository/PageRepository.php','app/zoosper-site/src/Repository/SiteDomainRepository.php','app/zoosper-site/src/Repository/SiteRepository.php','app/zoosper-url-rewrite/src/Repository/UrlRewriteRepository.php'] as $file){$source=(string)file_get_contents($root.'/'.$file);expect($source)->not->toContain('->fetch();')->not->toContain('->fetchAll();');}});
