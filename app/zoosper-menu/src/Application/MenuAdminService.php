<?php
declare(strict_types=1);
namespace Zoosper\Menu\Application;
use InvalidArgumentException; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
final readonly class MenuAdminService {
 public function __construct(private MenuAdminRepositoryInterface $menus){}
 /** @param array<string,mixed> $form */ public function saveMenu(array $form,?int $id=null): int{$site=(int)($form['site_id']??0);$code=strtolower(trim((string)($form['code']??'')));$label=trim((string)($form['label']??''));if($site<1||$label===''||!preg_match('/^[a-z0-9][a-z0-9_-]*$/',$code))throw new InvalidArgumentException('Site, label and a valid menu code are required.');return $this->menus->saveMenu($id,$site,$code,$label,$this->status($form));}
 /** @param array<string,mixed> $form */
 public function saveItem(int $menuId,array $form,?int $id=null): int
 {
  $label=trim((string)($form['label']??''));
  $page=$this->id($form['page_id']??null);
  $url=$this->normaliseExternalUrl((string)($form['url']??''));
  if($label===''||($page===null&&$url===null)){
   throw new InvalidArgumentException('Label and either a published page or a valid relative/HTTP(S) URL are required.');
  }
  return $this->menus->saveItem(
   $id,$menuId,$this->id($form['parent_id']??null),$page,$label,$url,
   in_array(($form['target']??''),['_self','_blank'],true)?(string)$form['target']:'_self',
   max(0,(int)($form['position']??0)),$this->status($form),
  );
 }
 private function normaliseExternalUrl(string $value): ?string
 {
  $value=trim(html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8'));
  if($value==='') return null;
  if(str_contains($value,'<')||str_contains($value,'>')||str_contains($value,'"')||str_contains($value,chr(39))||str_contains($value,'`')||str_contains($value,',')){
   throw new InvalidArgumentException('External URL must be plain text, not HTML or rich-text markup.');
  }
  if(str_starts_with($value,'/')){
   return str_starts_with($value,'//')?throw new InvalidArgumentException('Protocol-relative URLs are not supported.'): $value;
  }
  $parts=parse_url($value);
  if(!is_array($parts)||!in_array(strtolower((string)($parts['scheme']??'')),['http','https'],true)||empty($parts['host'])){
   throw new InvalidArgumentException('External URL must be an absolute HTTP(S) URL or a site-relative path beginning with /.');
  }
  return $value;
 }
 private function status(array $f): string{return ($f['status']??'active')==='inactive'?'inactive':'active';} private function id(mixed $v): ?int{$i=(int)$v;return $i>0?$i:null;}
}










