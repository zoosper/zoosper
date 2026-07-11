import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  // Critical for Zoosper: do not let Vite copy the public/ directory into an
  // outDir that also lives under public/. Without this, Vite can recursively
  // copy public/assets/admin/js into itself and produce ENAMETOOLONG paths.
  publicDir: false,
  build: {
    outDir: 'public/assets/admin/js',
    emptyOutDir: false,
    sourcemap: false,
    minify: true,
    lib: {
      entry: path.resolve(__dirname, 'assets/admin/editor/zoosper-editorjs-entry.js'),
      name: 'ZoosperEditorJsBundle',
      formats: ['iife'],
      fileName: () => 'editorjs.bundle.js'
    }
  }
});
