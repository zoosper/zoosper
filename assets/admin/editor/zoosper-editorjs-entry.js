import EditorJS from '@editorjs/editorjs';

// Expose Editor.js for Zoosper's small admin adapter.
// The textarea remains the submitted source of truth until the block_json phase.
window.EditorJS = EditorJS;
window.ZoosperEditorJsBundle = {
  EditorJS,
  version: 'phase-0.69'
};
