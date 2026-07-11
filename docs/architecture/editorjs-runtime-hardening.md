# Phase 0.73 - Editor.js Runtime Hardening and Admin Asset Cache Busting

This phase makes the Editor.js runtime safer and more observable.

## Key change

The textarea is hidden only after `editor.isReady` resolves. If Editor.js fails to initialise, the textarea remains visible and usable.

Admin assets now include a version query string so browser cache does not keep stale editor JavaScript after deployment.
