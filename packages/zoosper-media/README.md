# Zoosper Media

The Media package owns Admin media routes, validated upload orchestration, storage, metadata persistence, Editor.js image integration and optional derivative-processing contracts.

Admin and Editor.js uploads share the container-configured upload service. Persistence failure after storage invokes stored-file cleanup. Upload-time derivatives remain disabled until an explicit processor and enablement policy are configured.

The package is registered as a Composer `zoosper-module` with `extra.marko.module` set to `true`. See the canonical [user guide](../../docs/user-guide.md) and [module guide](../../docs/modules.md).
