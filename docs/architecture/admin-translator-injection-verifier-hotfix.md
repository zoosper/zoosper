# Phase 1.00.1 - Admin Translator Injection Verifier Hotfix

Phase 1.00 correctly moved the manifest loader before the controller provider runtime call, but the verifier compared against the first `ControllerProviderLoader` string in the file. That first match can be a `use` statement, not the runtime instantiation.

This phase scopes the verifier to runtime calls:

```text
(new ServiceProviderLoader(...))->register()
(new \Zoosper\Core\Bootstrap\ServiceProviderManifestLoader(...))->load(...)
(new ControllerProviderLoader(...))->load()
```
