# Error Logging

**Introduced in:** Phase 1.27.

Zoosper logs failures in **two complementary layers** so nothing fails silently.

## Layer 1 - Central router safety net (uncaught exceptions)

`Router::dispatch()` wraps every handler call in a `try/catch (Throwable)`. If a
controller or handler throws and does **not** handle it, the router:

1. logs it via `ErrorHandler::logException()` to `var/log/exception.log`
   (secrets are redacted by `LocalLogger`), and
2. returns a **safe 500** - an HTML page for normal requests, or a JSON body for
   `/api/` requests.

This means **every** route benefits automatically - no per-controller code needed.

## Layer 2 - Controller-level logging (caught exceptions)

Some controllers deliberately **catch** an exception to show the user a friendly
form (HTTP 422) instead of a blank 500. Those catches must **log first**:

```php
} catch (RuntimeException $exception) {
    $this->errorHandler?->logException($exception, ['controller' => 'PageAdminController', 'action' => 'create']);
    // ... render the friendly 422 form ...
}
```

## Why the earlier HY093 error was invisible

`PDOException extends RuntimeException`. The page/user controllers caught
`RuntimeException` to render the 422 form and showed `$exception->getMessage()` -
but never logged it. So a real database error (the missing `:locale` binding) was
shown to the user yet **absent from the log file**. Layer 2 fixes this for the
page controller; Layer 1 is the backstop for anything still uncaught.

## Guidance

- If a controller catches an exception to render a friendly page, **log it via
  `$this->errorHandler?->logException()` first**.
- Anything left uncaught is caught and logged centrally by the router.
- Prefer letting truly unexpected errors bubble to the router (Layer 1) rather
  than swallowing them.

## PCI note

`LocalLogger` redacts any context key containing `password`, `token`, `secret`,
or `session`. Never log OTPs, TOTP secrets, recovery codes, payment data or raw
reset tokens. The safe 500 pages never echo exception details to the browser.
