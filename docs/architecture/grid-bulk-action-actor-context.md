# Grid bulk-action actor context

The Page publication source confirms that `PageRepository::publish()` requires the acting admin-user ID, `PagePublishedEvent` carries page and admin-user IDs, and `AuditLoggerInterface::logAction()` accepts actor ID and email. The original shared executor contract carried only selected row identities, so a feature executor could not perform these established responsibilities without reading session globals.

`GridBulkActor` and `GridBulkExecutionContext` now carry trusted authenticated identity from the HTTP integration to the feature executor. Server dispatch fails closed when this context is absent. The context is not parsed from form input and cannot be supplied by browser fields.

This phase fixes the shared contract only. It does not expose Publish selected. The next Page integration can use the actor ID for repository mutation and events and the actor ID/email for audit logging.
