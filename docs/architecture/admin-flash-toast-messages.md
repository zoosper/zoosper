# Phase 0.67 - Admin flash/toast message foundation

Admin actions now have a reusable message system for save/publish feedback.

## Components

```text
FlashMessage
FlashMessageStoreInterface
SessionFlashMessageStore
FlashMessageRenderer
```

## Design

Redirect-based form flows use session flash messages. Messages are deduplicated by key so repeated saves do not pile up.

Future AJAX save endpoints can return the same message data shape as JSON and reuse the same frontend toast container.
