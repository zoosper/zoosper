<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Controller;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PDO;
use Zoosper\AdminGrid\AdminCollectionGrid;
use Zoosper\AdminGrid\AdminCollectionGridQuery;
use Zoosper\Auth\Admin\Grid\AccessToken\AccessTokenGrid;
use Zoosper\Auth\Admin\PersonalAccessTokenScopePresenter;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\Token\PersonalAccessTokenRepository;
use Zoosper\Auth\Token\PersonalAccessTokenService;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Self-owned, session-authenticated Personal Access Token administration. */
final readonly class PersonalAccessTokenAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private PersonalAccessTokenRepository $tokens,
        private PersonalAccessTokenService $issuer,
        private AdminViewRendererInterface $views,
        private AdminUrlGenerator $urls,
        private AuditLoggerInterface $audit,
        private PersonalAccessTokenScopePresenter $scopePresenter,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AdminCollectionGrid $collectionGrid = null,
        private ?PDO $pdo = null,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->render(request: $request);
    }

    public function create(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect($this->urls->url('login'));
        }

        $form = $request->form();
        $scopes = $form['scopes'] ?? [];
        $scopes = is_array($scopes) ? array_values(array_filter($scopes, 'is_string')) : [];

        try {
            $expires = $this->expiry(isset($form['expires_at']) ? (string) $form['expires_at'] : '');
            $issued = $this->issuer->issue($user->id, (string) ($form['name'] ?? ''), $scopes, $expires);
        } catch (InvalidArgumentException $exception) {
            return $this->render(error: $exception->getMessage(), submitted: $form, status: 422);
        }

        $this->audit->logAction(
            $user->id,
            $user->email,
            'personal_access_token.issued',
            'personal_access_token',
            (string) $issued['id'],
            'Issued Personal Access Token.',
            [
                'token_id' => $issued['id'],
                'public_id' => $issued['public_id'],
                'name' => trim((string) ($form['name'] ?? '')),
                'scopes' => $scopes,
                'expires_at' => $expires,
            ],
        );

        return $this->render(oneTimeToken: $issued['token']);
    }

    public function revoke(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect($this->urls->url('login'));
        }

        $id = (int) $request->routeParam('id', '0');
        $token = null;
        foreach ($this->tokens->allForUser($user->id) as $owned) {
            if ($owned->id === $id) {
                $token = $owned;
                break;
            }
        }

        if ($token === null || !$this->tokens->revoke($id, $user->id, gmdate('Y-m-d H:i:s'))) {
            $this->flash?->error('Token could not be revoked.', 'admin.pat.revoke');
            return Response::redirect($this->urls->url('access-tokens'));
        }

        $this->audit->logAction(
            $user->id,
            $user->email,
            'personal_access_token.revoked',
            'personal_access_token',
            (string) $id,
            'Revoked Personal Access Token.',
            [
                'token_id' => $id,
                'public_id' => $token->publicId,
                'name' => $token->name,
                'scopes' => $token->scopes,
            ],
        );
        $this->flash?->success('Personal Access Token revoked.', 'admin.pat.revoke');

        return Response::redirect($this->urls->url('access-tokens'));
    }

    /** @param array<string, mixed> $submitted */
    private function render(
        ?string $oneTimeToken = null,
        ?string $error = null,
        array $submitted = [],
        int $status = 200,
        ?Request $request = null,
    ): Response {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect($this->urls->url('login'));
        }

        $gridHtml = null;
        if ($request !== null && $this->collectionGrid !== null && $this->pdo !== null) {
            $grid = new AccessTokenGrid($this->pdo, $user->id, $this->urls, $this->csrf->token());
            $definition = $grid->definition();
            $gridHtml = $this->collectionGrid->render(
                $user->id,
                AccessTokenGrid::KEY,
                $this->urls->url('access-tokens'),
                $definition,
                $grid,
                AdminCollectionGridQuery::values($request, $definition),
                AdminCollectionGridQuery::bookmark($request),
            )['html'];
        }

        return Response::html(
            $this->views->render(
                'Personal Access Tokens',
                'zoosper-auth::admin/access-tokens/index',
                [
                    'tokens' => $gridHtml === null ? $this->tokens->allForUser($user->id) : [],
                    'gridHtml' => $gridHtml,
                    'scopeGroups' => $this->scopePresenter->groups(PersonalAccessTokenService::SCOPES),
                    'csrfToken' => $this->csrf->token(),
                    'createUrl' => $this->urls->url('access-tokens/create'),
                    'revokeUrl' => fn (int $id): string => $this->urls->url("access-tokens/{$id}/revoke"),
                    'oneTimeToken' => $oneTimeToken,
                    'error' => $error,
                    'submitted' => $submitted,
                ],
                $user,
                'access-tokens',
            ),
            $status,
        );
    }

    private function expiry(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            $date = new DateTimeImmutable($value, new DateTimeZone(date_default_timezone_get()));
        } catch (\Throwable) {
            throw new InvalidArgumentException('Token expiry is invalid.');
        }

        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }
}










