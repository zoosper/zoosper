# Phase 0.31 - Assets namespace migration

## Purpose

Move static assets away from application route namespaces such as:

```text
/admin/*
/frontend/*
```

and towards:

```text
/assets/admin/*
/assets/frontend/*
/assets/modules/<module>/*
```

## Benefits

- `/admin` remains a pure application route.
- Static directories no longer collide with application routes.
- A future dynamic `ADMIN_PATH` does not require static asset URL changes.
- Nginx config becomes simpler and more secure.

## PCI-aware rule

Static asset paths/config must never include OTP values, TOTP secrets, recovery codes, session IDs, payment details or customer-sensitive data.
