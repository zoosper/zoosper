# Phase 7B: API Grid reliability hardening source capture

## Purpose

Phase 7A established the second-pilot boundary and confirmed that a real second endpoint must not be invented. Phase 7B captures the exact current implementation before modifying reliability contracts.

The export includes the generic Grid, Admin Grid, API Grid and Store Orders pilot source, tests, package manifests and a focused search for timeout, decoding, response-size, redaction, cursor, pagination, export, schema, retry and rate-limit behaviour.

## Why capture before implementation

Reliability behaviour crosses transport, mapping, result, controller and presentation boundaries. A blind patch could duplicate an existing exception model, weaken redaction, or make remote failures look like empty results. The generated export is the implementation source of truth for the next bulk hardening patch.

## Next implementation gate

The next build should use the captured source to add missing behaviour only, with tests for:

- invalid JSON classification;
- timeout and non-success response classification;
- bounded response bodies;
- redacted diagnostics;
- opaque cursor handling;
- capability-gated bounded export;
- schema-drift errors that do not dump payloads.

No real second pilot endpoint, credential or production payload is introduced by this capture phase.
