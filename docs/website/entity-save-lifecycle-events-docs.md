# Entity save lifecycle events docs seed

Module authors should use save lifecycle listeners to validate, mutate or react to entity save data without changing core controllers.

Recommended use:

- `validate.before` for validation preparation.
- `validate.after` for validation inspection.
- `save.before` for final data mutation before persistence.
- `save.after` for post-save reactions.
- `commit.after` for side effects after successful persistence.
