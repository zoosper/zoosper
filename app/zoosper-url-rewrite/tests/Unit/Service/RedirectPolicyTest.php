<?php

declare(strict_types=1);
use Zoosper\UrlRewrite\Service\RedirectPolicy;
it('normalises safe redirects',function(){$p=new RedirectPolicy();expect($p->validate('old/path/','/new/path',301))->toBe(['source'=>'/old/path','target'=>'/new/path','type'=>301])->and($p->validate('/campaign','https://example.test/landing',302)['type'])->toBe(302);});
it('rejects invalid redirects',function(string $s,string $t,int $type){expect(fn()=>(new RedirectPolicy())->validate($s,$t,$type))->toThrow(InvalidArgumentException::class);})->with([['/same','/same',301],['/old','javascript:bad',301],['/old','/new',307],['/admin/login','/new',301],['/sitemap.xml','/new',301]]);
