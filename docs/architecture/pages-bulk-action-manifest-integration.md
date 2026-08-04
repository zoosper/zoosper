# Pages bulk-action manifest integration

Pages contributes `PageGridBulkActions::definitions()` for the `admin.pages` Grid. The only declaration is the already-supported `export.selected` client download. `PageAdminController` renders the declaration through the shared `GridBulkActionManifestRenderer`; Pages contains no dropdown, CSV, selection or browser execution logic.

The browser manifest is now the source of the visible bulk-action option. No server or remote mutation is introduced. Unknown execution contracts remain ignored by the shared browser adapter.
