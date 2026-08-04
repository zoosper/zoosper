# Page Publish selected side effects

`PagePublishSideEffects` implements the Phase 7V seam using the established `EventDispatcherInterface`, `PageEvents::PUBLISHED`, `PagePublishedEvent` and `AuditLoggerInterface::logAction()` contracts.

Both event and audit dependencies are mandatory constructor arguments. There is no nullable or silent fallback. After a draft Page is persisted as published, the component dispatches the established Page publication event and records one `page.bulk_publish` audit action for that mutated Page. Already-published Pages do not reach this component.

The audit metadata records the shared action ID, selected count, Page and Site identities, previous status and new status. Actor identity comes only from the trusted `GridBulkExecutionContext`.

This phase does not register the executor, expose a route or render Publish selected. Activation remains deferred until the protected controller composition can require both dependencies and build the authenticated actor context.
