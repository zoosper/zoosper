import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
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
