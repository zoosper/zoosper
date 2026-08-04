# Page Publish selected executor

The Page module now owns an isolated `PagePublishSelectedExecutor`. It preflights every explicit identity before any write, rejects malformed or missing Pages, skips already-published Pages so `published_at` is not reset, publishes draft Pages with the trusted actor ID and returns selected, published and skipped counts.

`PagePublishSideEffectsInterface` is an explicit seam for the established `PagePublishedEvent` and `AuditLoggerInterface` calls. This phase deliberately does not bind that seam, register the action, expose a route or add browser controls. The next integration phase must implement the side effects against the exact runtime event dispatcher, then register the executor only when audit infrastructure is present.

The repository has no transaction abstraction, so the executor does not claim atomic rollback across persistence, event dispatch and audit logging. Full preflight prevents missing-record partial updates.
