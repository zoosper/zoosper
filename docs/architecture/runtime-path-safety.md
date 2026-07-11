# Phase 0.75 - Runtime Path Safety

Runtime/generated files must never be created under `public/`.

This phase introduces `ProjectPathResolver` and ensures HTMLPurifier cache paths resolve under project `var/`, not relative to PHP's current working directory.

## Rule

```text
GOOD: var/cache/htmlpurifier
BAD:  public/var/cache/htmlpurifier
```
