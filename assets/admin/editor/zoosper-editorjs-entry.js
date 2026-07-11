import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import EditorjsList from '@editorjs/list';

// Expose Editor.js and approved first-party tools for Zoosper's admin adapter.
// The textarea remains the submitted source of truth until the block_json phase.
window.EditorJS = EditorJS;
window.ZoosperEditorJsBundle = {
  EditorJS,
  Header,
  EditorjsList,
  version: 'phase-0.74'
};
