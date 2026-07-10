# TwoFactor namespace autoload hotfix

## Symptom

```text
Class "Zoosper\TwoFactor\Repository\AdminTwoFactorResetRepository" not found
```

while `Zoosper\Mail` classes are now autoloading correctly.

## Cause

Composer is aware of the new mail namespace, but the `Zoosper\TwoFactor\` PSR-4 namespace is still missing or not aligned with the module path.

## Fix

Add this mapping to `composer.json`:

```json
"Zoosper\\TwoFactor\\": "app/zoosper-two-factor/src/"
```

Then run:

```bash
composer dump-autoload
```

## PCI-aware note

This only changes autoload configuration. It does not expose or log OTPs, TOTP secrets, recovery-code plaintext or provisioning data.
