<?php

declare(strict_types=1);

namespace Zoosper\Auth\Security;

/**
 * Minimum viable admin password policy (Sonnet Phase 2 §5).
 *
 * Previously the only enforcement anywhere in the codebase was a non-empty
 * string check in UserAdminController::create() — a one-character password was
 * accepted. This adds a real, testable policy: minimum length plus a mix of
 * character classes, checked BEFORE any database write.
 *
 * Deliberately self-contained (no config/DI wiring): the thresholds are
 * conservative constants for now. If/when config/security.php should drive
 * this, inject the values via a constructor rather than changing the defaults
 * silently — that keeps this class trivially unit-testable without a
 * ConfigRepository.
 */
final class PasswordPolicy
{
    public function __construct(
        private readonly int $minLength = 12,
        private readonly int $minCharacterClasses = 2,
    ) {
    }

    /**
     * Return a list of human-readable violation messages. An empty list means
     * the password satisfies the policy.
     *
     * @return list<string>
     */
    public function violations(string $password): array
    {
        $violations = [];

        if (mb_strlen($password) < $this->minLength) {
            $violations[] = sprintf('Password must be at least %d characters long.', $this->minLength);
        }

        $classesPresent = 0;
        foreach ($this->characterClassPatterns() as $pattern) {
            if (preg_match($pattern, $password) === 1) {
                $classesPresent++;
            }
        }

        if ($classesPresent < $this->minCharacterClasses) {
            $violations[] = sprintf(
                'Password must include at least %d of: lowercase letters, uppercase letters, numbers, symbols.',
                $this->minCharacterClasses,
            );
        }

        return $violations;
    }

    public function isValid(string $password): bool
    {
        return $this->violations($password) === [];
    }

    /**
     * @return list<string> regex patterns, one per character class
     */
    private function characterClassPatterns(): array
    {
        return [
            '/[a-z]/',
            '/[A-Z]/',
            '/[0-9]/',
            '/[^a-zA-Z0-9]/',
        ];
    }
}
