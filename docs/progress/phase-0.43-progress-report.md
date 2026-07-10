# Phase 0.43 progress report

| Feature | Status | Notes |
|---|---|---|
| SMTP configuration | Working | Mailpit delivery was confirmed. |
| CLI `.env` loading | Working | CLI now uses MySQL from `.env`. |
| MySQL-first policy | Working | Database diagnostics passed on MySQL. |
| 2FA reset CLI | Working | Reset completed successfully and schema verifies. |
| Admin 2FA reset UX | Working | Status messaging was added. |
| 2FA enrolment foundation | Added | Secret generation, OTP verify, protected storage and recovery-code hashing exist. |
| 2FA setup routes | Added | Module-owned `config/admin_routes.php` included in this phase. |
| Login redirect to setup | Pending full replacement | Requires latest auth/route loader files to avoid guessing current code. |
| SMTP logs manager | Roadmap | Add searchable email log grid and content/status view in a future phase. |
