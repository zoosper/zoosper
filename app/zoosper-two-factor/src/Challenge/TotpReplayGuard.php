<?php
declare(strict_types=1);
namespace Zoosper\TwoFactor\Challenge;
use Closure;
use Zoosper\TwoFactor\Totp\TotpVerifier;
/**
 * Couples TOTP verification to atomic per-user time-step consumption.
 *
 * @psalm-suppress UnusedClass Resolved through the module service manifest.
 */
final readonly class TotpReplayGuard
{
    public function __construct(
        private TotpVerifier $verifier,
        private AdminTotpReplayRepository $repository,
        private ?Closure $clock = null,
    ) {}
    public function claim(int $adminUserId, string $secret, string $code): bool
    {
        $timestamp = $this->clock !== null ? (int) ($this->clock)() : time();
        $counter = $this->verifier->matchingCounter($secret, $code, $timestamp);
        if ($counter === null) {
            return false;
        }
        return $this->repository->claimIfNewer($adminUserId, $counter, gmdate('Y-m-d H:i:s', $timestamp));
    }
}
