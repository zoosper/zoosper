# Phase 1.19.2 - Error Notice Selector Alias Hotfix

Phase 1.19.1 fixed roadmap wording, but the suite still failed because the CSS contained red error styling without the standard `.notice-error` selector.

This hotfix adds the canonical selector alias:

```css
.notice.notice-error,
.notice-error
```

so rendered admin error markup can be styled consistently and verified reliably.
