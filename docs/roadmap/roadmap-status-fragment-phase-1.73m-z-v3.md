## Phase 1.73m-z v3: Site lookup service binding fix

Status: ready to apply

Replaces the service-binding patcher with a fully-qualified-class-name version and updates the audit to accept the final binding shape.

Safety:

- Dry-run by default.
- Backup before apply.
- Binding lives in the Site module service config.
- Core runtime source remains decoupled.
