# Phase 0.41 - Admin 2FA reset UX polish

## Goal

Make the admin user edit screen clearly show whether a 2FA reset succeeded, failed or was unavailable.

## Changes

- Reset action redirects with a non-sensitive notice code.
- Edit form renders success/error notices from the notice code.
- Reset failure does not expose secret material or raw exception details.
- Added MySQL-only repair tool for columns that existed in the intended schema but were missing from already-created tables.

## PCI-aware handling

The UI and tools never display or log OTP values, TOTP secrets, recovery-code plaintext, provisioning URIs, QR data, SMTP passwords or reset tokens.
