import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import EditorjsList from '@editorjs/list';
import ImageTool from '@editorjs/image';

// Expose Editor.js and approved first-party tools for Zoosper's admin adapter.
// The textarea remains the submitted HTML fallback while content_json captures
// the structured document for safe server-side rendering.
window.EditorJS = EditorJS;
window.ZoosperEditorJsBundle = {
  EditorJS,
  Header,
  EditorjsList,
  ImageTool,
  version: 'phase-1.37l'
};
