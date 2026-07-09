<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class LoginController
{
    public function __construct(private AuthService $auth, private SessionGuard $guard, private CsrfTokenManager $csrf, private LoginHistoryRepository $loginHistory)
    {
    }

    public function show(Request $request): Response
    {
        if ($this->guard->user() !== null) {
            return Response::redirect('/admin');
        }
        return Response::html($this->form());
    }

    private function form(?string $error = null): string
    {
        $token = htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8');
        $errorHtml = $error !== null ? '<p style="color:#b91c1c">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p>' : '';
        return <<<HTML
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Zoosper Admin Login</title><style>body{font-family:system-ui;margin:3rem;color:#102a43}.card{max-width:420px;padding:2rem;border:1px solid #d9e2ec;border-radius:16px}label{display:block;margin-top:1rem}input{width:100%;padding:.7rem;margin-top:.25rem}button{margin-top:1.25rem;padding:.75rem 1rem}</style></head><body><main class="card"><h1>Zoosper Admin</h1>{$errorHtml}<form method="post" action="/admin/login"><input type="hidden" name="_csrf_token" value="{$token}"><label>Email <input type="email" name="email" required autocomplete="username"></label><label>Password <input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Log in</button></form></main></body></html>
HTML;
    }

    public function login(Request $request): Response
    {
        $form = $request->form();
        $email = (string)($form['email'] ?? '');
        if (!$this->csrf->isValid((string)($form['_csrf_token'] ?? ''))) {
            $this->loginHistory->record(null, $email, 'csrf_failed', $request->clientIp(), $request->userAgent());
            return Response::html($this->form('Invalid security token.'), 419);
        }

        $user = $this->auth->authenticate($email, (string)($form['password'] ?? ''));
        if ($user === null) {
            $this->loginHistory->record(null, $email, 'failed', $request->clientIp(), $request->userAgent());
            return Response::html($this->form('Invalid email or password.'), 401);
        }

        $this->guard->login($user);
        $this->loginHistory->record($user->id, $user->email, 'success', $request->clientIp(), $request->userAgent());
        return Response::redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $form = $request->form();
        if (!$this->csrf->isValid((string)($form['_csrf_token'] ?? ''))) {
            return Response::html('Invalid security token.', 419);
        }
        $this->guard->logout();
        return Response::redirect('/admin/login');
    }
}
