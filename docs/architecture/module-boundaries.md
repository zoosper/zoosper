# Module boundaries

## Media and Page composition boundary

`zoosper-media` owns media storage, validation, metadata and Editor.js image contracts. It consumes Core and Auth contracts and does not import concrete Admin implementations.

`zoosper-page` owns page persistence, structured-content rendering and Page Admin orchestration. The package explicitly declares Media because `config/services.php` composes `EditorJsImageBlockSanitizer`. Admin layout/view rendering remains interface-based, while Page-specific editor, form and Grid integration continues to justify the current Admin dependency.
