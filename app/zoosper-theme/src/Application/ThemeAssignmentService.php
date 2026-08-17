<?php
declare(strict_types=1);
namespace Zoosper\Theme\Application;
use RuntimeException;use Zoosper\Site\Repository\SiteRepository;use Zoosper\Theme\Theme\ThemeRepository;
final readonly class ThemeAssignmentService{public function __construct(private ThemeRepository $themes,private SiteRepository $sites){}public function assign(int $siteId,string $themeCode):void{if($siteId<=0||$this->sites->findById($siteId)===null)throw new RuntimeException('Site does not exist.');if(!$this->themes->exists($themeCode))throw new RuntimeException('Theme does not exist: '.$themeCode);$this->sites->updateTheme($siteId,$themeCode);}}
