# Admin Content Editor Strategy

Phase 0.27 adds configuration for a rich editor strategy without wiring a specific JavaScript editor into existing forms yet.

## Options reviewed

- Tiptap is described by its official site as an open-source rich text editor framework and its OSS page states the editor is free, open source and MIT licensed.
- Editor.js describes itself as a free, open-source block-style editor with clean JSON output and notes that JSON output is easy to sanitise and extend.

## Recommended starting direction

For large CMS-page bodies, start with Editor.js as an optional admin editor because it stores structured JSON instead of raw HTML and can be hidden behind a toggle. Tiptap remains a good later option when the preferred storage model is rich HTML or ProseMirror JSON.

## Security posture

- Keep textarea fallback available.
- Keep WYSIWYG disabled by default until asset loading and sanitisation are wired.
- Sanitise rendered output server-side.
- Do not put payment, OTP, recovery-code or TOTP-secret data into editor payloads or logs.
