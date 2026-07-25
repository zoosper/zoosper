# Retired Tools

## 2026-07-25T09:06:29+00:00 — Phase 1.80 Tools Hygiene Cleanup

Mode: `dry-run`

This entry records one-off helper scripts identified by `tools/gate.php --strict` as hygiene warnings.

### Planned retirement

- `tools/apply-admin-form-config-aggregator-layered-loader.php` — 3970 bytes, sha256 `80140395321a0011452a353eec69aa277fa263a0eee4b5c7b65c60441903b478`
- `tools/apply-admin-form-config-layered-loader.php` — 3071 bytes, sha256 `388f1d2f4e5943fe2c32eacf72f914346796c348d1e7f28895509e457d4f85d2`
- `tools/apply-composer-internal-package-stability.php` — 6405 bytes, sha256 `5b8c99c8a9cb6f1a3265a99cf0aa11c078faa93803aecf04ef2369f8badd2f0a`
- `tools/apply-composer-local-package-repositories.php` — 6863 bytes, sha256 `752e2d295bd6298b45a6839c189f6116a921239610e10409bf12590e3a483617`
- `tools/apply-rate-limit-admin-login-policy.php` — 5189 bytes, sha256 `35b63b593a32e02cd9f8eff6ad985e75d76d7d8d88ede287099bd6e2dc1f3e87`
- `tools/apply-rate-limit-admin-middleware-hook.php` — 5260 bytes, sha256 `325c67b8152ec0e32b190779210c204e0c795f387becd2ae7545eecbe6dc08d1`
- `tools/apply-role-admin-latte-cutover.php` — 1608 bytes, sha256 `82a4844118772c9cb378fcd2cbde3c9af6bb4e19ccbc02cf31c9da2aca7d972a`
- `tools/apply-role-admin-markup-view-cutover.php` — 1292 bytes, sha256 `ecf0723ed63f668fe6bf51a17fec53a4794ddad86cd2e49a936d1ac3e615882f`
- `tools/apply-site-lookup-service-binding.php` — 5444 bytes, sha256 `b0f62d31bb191ae64d7fd3dd54af0c440ff9adadd4e1e5f4e8ed13f2593f8215`
- `tools/cleanup-expired-rate-limit-buckets.php` — 3168 bytes, sha256 `9f5e3f3a7982c398481471c882efe2f50208855bc8fe293e3b5029922b1b56b4`
- `tools/cleanup-page-momentum-post-runtime-support-artifacts.php` — 9257 bytes, sha256 `d2aa33b88e6c0a79b4f5bb19b80efe21a69a7b932ed2abda07abdf71e7c65ac2`
- `tools/cleanup-page-momentum-process-artifacts.php` — 8377 bytes, sha256 `37148ab8b02944a9ad181afe0d8ca9348078aba361d052f84f3fb7b286e5efaa`
- `tools/cleanup-page-momentum-support-artifacts.php` — 7026 bytes, sha256 `8fed897dfde4bb9777aa67d39d825133ad09790e1bed64810be5077b0ebd3679`

## 2026-07-25T09:06:35+00:00 — Phase 1.80 Tools Hygiene Cleanup

Mode: `apply`

This entry records one-off helper scripts identified by `tools/gate.php --strict` as hygiene warnings.

### Planned retirement

- `tools/apply-admin-form-config-aggregator-layered-loader.php` — 3970 bytes, sha256 `80140395321a0011452a353eec69aa277fa263a0eee4b5c7b65c60441903b478`
- `tools/apply-admin-form-config-layered-loader.php` — 3071 bytes, sha256 `388f1d2f4e5943fe2c32eacf72f914346796c348d1e7f28895509e457d4f85d2`
- `tools/apply-composer-internal-package-stability.php` — 6405 bytes, sha256 `5b8c99c8a9cb6f1a3265a99cf0aa11c078faa93803aecf04ef2369f8badd2f0a`
- `tools/apply-composer-local-package-repositories.php` — 6863 bytes, sha256 `752e2d295bd6298b45a6839c189f6116a921239610e10409bf12590e3a483617`
- `tools/apply-rate-limit-admin-login-policy.php` — 5189 bytes, sha256 `35b63b593a32e02cd9f8eff6ad985e75d76d7d8d88ede287099bd6e2dc1f3e87`
- `tools/apply-rate-limit-admin-middleware-hook.php` — 5260 bytes, sha256 `325c67b8152ec0e32b190779210c204e0c795f387becd2ae7545eecbe6dc08d1`
- `tools/apply-role-admin-latte-cutover.php` — 1608 bytes, sha256 `82a4844118772c9cb378fcd2cbde3c9af6bb4e19ccbc02cf31c9da2aca7d972a`
- `tools/apply-role-admin-markup-view-cutover.php` — 1292 bytes, sha256 `ecf0723ed63f668fe6bf51a17fec53a4794ddad86cd2e49a936d1ac3e615882f`
- `tools/apply-site-lookup-service-binding.php` — 5444 bytes, sha256 `b0f62d31bb191ae64d7fd3dd54af0c440ff9adadd4e1e5f4e8ed13f2593f8215`
- `tools/cleanup-expired-rate-limit-buckets.php` — 3168 bytes, sha256 `9f5e3f3a7982c398481471c882efe2f50208855bc8fe293e3b5029922b1b56b4`
- `tools/cleanup-page-momentum-post-runtime-support-artifacts.php` — 9257 bytes, sha256 `d2aa33b88e6c0a79b4f5bb19b80efe21a69a7b932ed2abda07abdf71e7c65ac2`
- `tools/cleanup-page-momentum-process-artifacts.php` — 8377 bytes, sha256 `37148ab8b02944a9ad181afe0d8ca9348078aba361d052f84f3fb7b286e5efaa`
- `tools/cleanup-page-momentum-support-artifacts.php` — 7026 bytes, sha256 `8fed897dfde4bb9777aa67d39d825133ad09790e1bed64810be5077b0ebd3679`

### Deleted

- `tools/apply-admin-form-config-aggregator-layered-loader.php`
- `tools/apply-admin-form-config-layered-loader.php`
- `tools/apply-composer-internal-package-stability.php`
- `tools/apply-composer-local-package-repositories.php`
- `tools/apply-rate-limit-admin-login-policy.php`
- `tools/apply-rate-limit-admin-middleware-hook.php`
- `tools/apply-role-admin-latte-cutover.php`
- `tools/apply-role-admin-markup-view-cutover.php`
- `tools/apply-site-lookup-service-binding.php`
- `tools/cleanup-expired-rate-limit-buckets.php`
- `tools/cleanup-page-momentum-post-runtime-support-artifacts.php`
- `tools/cleanup-page-momentum-process-artifacts.php`
- `tools/cleanup-page-momentum-support-artifacts.php`

## 2026-07-25T09:18:16+00:00 - Phase 1.83 Page Momentum Cleanup Tools Retirement

Mode: `dry-run`

These page-momentum cleanup helpers were restored by the broad Git-history recovery, but remained hygiene warnings and are not test-protected durable tools.

### Planned retirement

- `tools/cleanup-page-momentum-post-runtime-support-artifacts.php` - 9257 bytes, sha256 `d2aa33b88e6c0a79b4f5bb19b80efe21a69a7b932ed2abda07abdf71e7c65ac2`
- `tools/cleanup-page-momentum-process-artifacts.php` - 8377 bytes, sha256 `37148ab8b02944a9ad181afe0d8ca9348078aba361d052f84f3fb7b286e5efaa`
- `tools/cleanup-page-momentum-support-artifacts.php` - 7026 bytes, sha256 `8fed897dfde4bb9777aa67d39d825133ad09790e1bed64810be5077b0ebd3679`

## 2026-07-25T09:18:22+00:00 - Phase 1.83 Page Momentum Cleanup Tools Retirement

Mode: `apply`

These page-momentum cleanup helpers were restored by the broad Git-history recovery, but remained hygiene warnings and are not test-protected durable tools.

### Planned retirement

- `tools/cleanup-page-momentum-post-runtime-support-artifacts.php` - 9257 bytes, sha256 `d2aa33b88e6c0a79b4f5bb19b80efe21a69a7b932ed2abda07abdf71e7c65ac2`
- `tools/cleanup-page-momentum-process-artifacts.php` - 8377 bytes, sha256 `37148ab8b02944a9ad181afe0d8ca9348078aba361d052f84f3fb7b286e5efaa`
- `tools/cleanup-page-momentum-support-artifacts.php` - 7026 bytes, sha256 `8fed897dfde4bb9777aa67d39d825133ad09790e1bed64810be5077b0ebd3679`

### Deleted

- `tools/cleanup-page-momentum-post-runtime-support-artifacts.php`
- `tools/cleanup-page-momentum-process-artifacts.php`
- `tools/cleanup-page-momentum-support-artifacts.php`

