# Authorised Grid bulk-action manifest

Registered definitions are filtered server-side through `GridBulkActionAuthoriser` before presentation. `GridBulkActionManifest` exposes only browser-relevant metadata and deliberately omits permission names. `GridBulkActionManifestRenderer` emits escaped inert JSON, not executable JavaScript.

The browser adapter accepts only the currently supported `export.selected` client-download contract. Unknown, server, remote and mutating definitions are ignored until dedicated executors exist. This phase establishes the registry-to-browser boundary; wiring the Pages declaration into its shared workspace is the next integration step.
