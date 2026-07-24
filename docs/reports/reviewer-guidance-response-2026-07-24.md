# Reviewer Guidance Response — 24 July 2026

## Context

A review was received after the Phase 1.41 and Phase 1.42 method plugin/interceptor work. The review focused on the `app/` directory and confirmed that the recent architecture direction is strong: the project is moving towards Magento-grade modularity while maintaining conservative runtime safety.

## Key review takeaways

The review positively confirmed the following:

- The method plugin/interceptor foundation is now a meaningful modularity pillar.
- Module-owned plugin discovery through `config/plugins.php` is the right direction.
- The container-aware resolver seam is useful because it avoids premature coupling to a concrete DI container.
- The report-only execution model is the right safety-first strategy.
- Keeping runtime interception disabled by default is the correct risk posture.
- `Zoosper\Page\Service\PageRenderer::render` is a logical candidate for future report-only proof work because it is high-impact for rendering, themes, blocks, caching, and extension behaviour.
- The invalid Closure admin middleware issue was a useful bootstrap/config drift lesson and should lead to better audit tooling.

## Recommended interpretation

The review should be treated as validation of the current architecture direction, not as a reason to rush into live interception.

The project should continue with the same pattern:

1. Discover/configure.
2. Prove defaults are disabled.
3. Add report-only observation.
4. Add fixture-only proof.
5. Audit and document rollback.
6. Only then consider real runtime integration.

## Strategic decision

The next phase should not jump to visible UI features yet. The current method plugin/config-runtime arc is close to becoming a durable extension foundation. It is worth finishing the safe configuration-discovery piece first.

Recommended next phase:

`Phase 1.43a-c: config-layered method plugin runtime configuration discovery`

This directly matches the reviewer guidance and connects the method plugin system to the already completed config-layering foundation.

## Guardrails for the next phase

Phase 1.43 must preserve these constraints:

- Runtime plugins remain disabled by default.
- Default invocation allow-list remains empty.
- Config discovery must not enable production interception accidentally.
- Root/project config must be able to override module defaults safely.
- If an allow-list exists in config, it must still be treated as report-only planning until explicit proof exists.
- No selected candidate method should be invoked as part of config discovery.

## Recommended near-term sequence

### Phase 1.43a-c

Config-layered method plugin runtime configuration discovery.

Goals:

- Define runtime plugin config shape.
- Discover module/root config sources.
- Prove merged config defaults remain disabled.
- Prove root/project override can disable or clear allow-list entries.
- Add audit tooling and tests.

### Phase 1.43d-f

Selected candidate signature and fixture contract refinement.

Goals:

- Inspect `PageRenderer::render` signature.
- Document required fixture argument types.
- Add placeholder fixture contract without invoking production service.
- Preserve disabled runtime defaults.

### Phase 1.43g-i

Bootstrap/config drift audit expansion.

Goals:

- Add a wider config shape audit for bootstrap-loaded config files.
- Include admin middleware, plugin runtime config, route/middleware config, and module config files.
- Prevent future Closure/non-string drift from reaching bootstrap.

## Recommendation for Mr G summary

The reviewer feedback supports the current direction. The project should keep moving through the method plugin/config runtime foundation, but continue avoiding production interception until enough fixture-based report-only proof exists.
