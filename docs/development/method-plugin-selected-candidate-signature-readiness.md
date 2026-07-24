# Phase 1.43d-f: Selected Candidate Signature and Fixture Argument Refinement

## Goal

Inspect the selected method plugin report-only candidate method signature and refine the fixture-input contract without invoking the selected production service.

Selected candidate:

```text
Zoosper\Page\Service\PageRenderer::render
```

## Safety model

- Reflection/signature discovery only.
- No service instance is invoked.
- Runtime config remains disabled by default.
- No invocation key is added to runtime allow-list.
- Refined fixture arguments are placeholders only.
- Output policy remains baseline-result-only.

## Verification

```bash
php8.5 tools/discover-method-plugin-selected-candidate-signature.php
php8.5 tools/refine-method-plugin-selected-candidate-fixture-contract.php
php8.5 tools/audit-method-plugin-selected-candidate-signature-readiness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginSelectedCandidateSignatureReadinessTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.43g-i should expand bootstrap/config drift audits across runtime-loaded config files, using the admin middleware Closure incident as the regression motivation.
