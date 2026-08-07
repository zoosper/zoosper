# Module boundaries

## Media and Page composition boundary

`zoosper-media` owns media storage, validation, metadata and Editor.js image contracts. It consumes Core and Auth contracts and does not import concrete Admin implementations.

`zoosper-page` owns page persistence, structured-content rendering and Page Admin orchestration. The package explicitly declares Media because `config/services.php` composes `EditorJsImageBlockSanitizer`. Admin layout/view rendering remains interface-based, while Page-specific editor, form and Grid integration continues to justify the current Admin dependency.

## Page to Admin compatibility boundary

`zoosper-page` retains an explicit dependency on `zoosper/admin`. The approved runtime boundary is limited to the content-editor contract, flash-message contract, and Admin form-configuration aggregation bridge. Concrete Admin editor, layout, and view implementations are not permitted in Page runtime. This is an intentional compatibility boundary, not hidden package coupling.

## Shared presentation contracts

Core owns `ContentEditorInterface`, `FlashMessageStoreInterface`, `AdminFormConfigAggregator`, and `AdminConfigLayeredFileLoader`. Admin owns concrete editor selection and the session-backed flash store. Feature modules consume only the Core contracts and do not require Admin for these abstractions.
