# Phase 0.44 progress report

| Feature | Status | Notes |
|---|---|---|
| SMTP configuration | Working | Mailpit delivery confirmed. |
| CLI `.env` loading | Working | CLI uses MySQL from `.env`. |
| 2FA reset CLI | Working | Schema verified and reset tool works. |
| Admin 2FA reset UX | Working | Status notices added. |
| 2FA enrolment foundation | Working foundation | Setup screen is reachable. |
| 2FA setup QR code | Added | Uses local QR dependency; manual setup key remains fallback. |
| Dynamic admin URL foundation | Started | Controller-generated links use `config/admin.php`; route paths still need route-loader/base-path phase. |
| Login redirect to setup | Pending | Next phase should wire login flow to redirect users without active 2FA. |
| SMTP logs manager | Roadmap | Future phase should add searchable email log grid and content/status view. |
