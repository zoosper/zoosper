# Phase 1.04.1 - Admin Translator Injected Runtime Verifier Hotfix

Phase 1.04 correctly changed `PageAdminController::t()` to prefer `AdminContextTranslatorResolver`, then fall back to the injected `TranslatorInterface` and finally to `IdentityTranslator`.

The previous injected-runtime verifier still expected the old Phase 1.01 pattern where the injected translator was the first branch. This hotfix updates the verifier to accept the new correct order:

```text
AdminContextTranslatorResolver
TranslatorInterface
IdentityTranslator
```
