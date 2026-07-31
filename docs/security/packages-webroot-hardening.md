# Packages source webroot hardening

Zoosper keeps first-party package source under `packages/`. This directory is
not a public asset root and must never be exposed by a web server whose document
root is accidentally configured above `public/`.

The source root is protected consistently in three layers:

- project structure policy requires `packages/` and forbids it beneath public;
- public webroot policy blocks `/packages/`;
- the Nginx hardening include returns 404 for `/packages/`.

The supported document root remains `public/`. These rules are defence in depth
for deployment mistakes and do not replace correct virtual-host configuration.
