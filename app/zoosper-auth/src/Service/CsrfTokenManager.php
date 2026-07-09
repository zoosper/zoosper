<?php
declare(strict_types=1);
namespace Zoosper\Auth\Service;
final readonly class CsrfTokenManager { public function token():string{ if(empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token']=bin2hex(random_bytes(32)); return (string)$_SESSION['_csrf_token']; } public function isValid(?string $token):bool{return is_string($token)&&hash_equals($this->token(),$token);} }
