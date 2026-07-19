# Zoosper enterprise modularity roadmap

This roadmap records Mr G's architectural guidance after the media package and Editor.js image pipeline work.

## Near-term priority

Finish the current media/editor arc with browser smoke validation before starting another large architecture stream.

```text
1.37m Media/editor browser smoke and UX polish
1.37n Media processing policy and derivative architecture
1.37o Prepare zoosper/media for true standalone repository workflow
```

## Strategic track A: service contracts and DTOs

Modules should progressively expose public APIs through interfaces instead of cross-module calls to concrete classes.

Planned direction:

```text
- Define service contracts for high-traffic module boundaries.
- Introduce typed DTOs for cross-module data exchange.
- Reduce associative-array payloads where they cross module boundaries.
```

## Strategic track B: plugin/interceptor system

Events are useful when the core team predicts extension points in advance. A plugin/interceptor layer gives third-party modules a safer way to adjust behaviour without overriding whole classes.

Planned direction:

```text
- Design before/after/around method interception.
- Restrict interception to public service contracts first.
- Add explicit ordering rules and diagnostics.
- Avoid intercepting internal/private hot paths until the contract model is stable.
```

## Strategic track C: API-first/headless evolution

Zoosper should keep server-rendered pages/admin working while growing API parity.

Planned direction:

```text
- Expand zoosper-api resource coverage.
- Expose structured content_json through APIs.
- Add webhooks for important events such as page published/unpublished.
- Treat a future SPA admin as a long-term side-by-side transition, not an immediate replacement.
```

## Strategic track D: asynchronous and storage abstractions

Media derivatives and heavier maintenance tasks should not permanently assume synchronous request processing.

Planned direction:

```text
- Introduce queue contracts before introducing workers.
- Add MediaProcessorInterface for thumbnails/WebP derivatives.
- Add storage abstraction before S3/Azure/GCS support.
- Keep uploaded originals immutable.
```

## Strategic track E: developer experience

DX is the adoption multiplier. Module scaffolding should become package-aware and test-aware.

Planned direction:

```text
- Supercharge make:module for packages/ output.
- Generate composer.json, module.php, config skeletons, tests and docs.
- Add service-contract and DTO templates.
- Support standalone module test execution.
- Later introduce metapackages such as zoosper/cms-core.
```

## Strategic track F: AI-ready core

Make the schema, services and docs easier for humans and LLM tools to inspect.

Planned direction:

```text
- Maintain machine-readable documentation indexes.
- Keep module manifests rich and consistent.
- Expose safe schema/API metadata for tooling.
- Avoid leaking secrets/runtime data into diagnostic outputs.
```
