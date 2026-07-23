# ADR: Config Layering

## Status

Proposed for Phase 1.40.

## Context

Zoosper is moving towards stronger package/module ownership. Modules need to ship sensible defaults, but projects must be able to override those defaults without forking module code.

## Decision

Introduce an explicit layered config loader where each layer has a source name and each later layer overrides earlier layers.

The initial merge primitive is additive and not yet wired into existing runtime loaders.

## Consequences

### Positive

- Clear precedence model.
- Modules can ship defaults safely.
- Root projects retain final control.
- Diagnostics can report which sources contributed.

### Negative

- Existing config loaders need gradual migration.
- Care is required to avoid changing list-array semantics for routes, middleware, or menu entries.

## Follow-up

- Add config source discovery audit.
- Migrate a low-risk config type first.
- Add documentation for module authors.
