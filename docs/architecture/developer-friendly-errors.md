# Phase 0.57 - Developer-friendly error diagnostics

## Goal

Zoosper should guide developers to fixes instead of showing only cryptic stack traces.

Helpful framework errors include:

```text
What went wrong
Context
Suggested solution
Docs link
Safe details
```

## Components

```text
Zoosper\Core\Exception\ZoosperException
Zoosper\Core\Exception\SensitiveValueRedactor
Zoosper\Core\Exception\ConsoleExceptionFormatter
```

## First integration points

```text
ServiceContainer
ServiceProviderLoader
ModuleDependencyValidator
ControllerProviderLoader
ModuleRouteLoader
ErrorHandler
```

## Security

Helpful errors must not expose credentials, session IDs, CSRF tokens, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values.
