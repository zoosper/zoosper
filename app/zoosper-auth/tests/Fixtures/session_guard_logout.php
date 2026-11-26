<?php

declare(strict_types=1);

namespace Zoosper\Auth\Service;

require dirname(__DIR__, 4) . '/vendor/autoload.php';

function session_status(): int
{
    return PHP_SESSION_ACTIVE;
}
function ini_get(string $name): string|false
{
    return $GLOBALS['cookie_setting'];
}
function session_name(): string|false
{
    return $GLOBALS['session_name'];
}
function session_get_cookie_params(): array
{
    return ['path' => '/admin', 'domain' => 'example.test', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict'];
}
function setcookie(string $name, string $value, array $options): bool
{
    $GLOBALS['cookie'] = ['name' => $name, 'value' => $value, 'options' => $options];
    return true;
}
function session_destroy(): bool
{
    $GLOBALS['destroyed'] = true;
    return true;
}

$GLOBALS['cookie_setting'] = $argv[1] === 'false' ? false : $argv[1];
$GLOBALS['session_name'] = match ($argv[2]) { 'false' => false, 'empty' => '', default => 'ADMINSESSID' };
$GLOBALS['cookie'] = null;
$GLOBALS['destroyed'] = false;
$_SESSION = ['admin_user_id' => 1, 'pending_2fa_user_id' => 1];
$guard = new SessionGuard(new \Zoosper\Auth\Repository\AdminUserRepository(new \PDO('sqlite::memory:')));
$guard->logout();
print json_encode(['session' => $_SESSION, 'destroyed' => $GLOBALS['destroyed'], 'cookie' => $GLOBALS['cookie']], JSON_THROW_ON_ERROR);