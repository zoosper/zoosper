# Phase 0.64 - Page content sanitisation

CMS page body HTML is now sanitised before create/update persistence in `PageAdminController`.

## Flow

```text
Admin submits CMS page content
  -> PageAdminController
  -> HtmlSanitizerInterface::sanitise()
  -> PageRepository::create/update()
  -> PageRenderer
  -> Latte template renders stored sanitised HTML with |noescape
```

## Why save-time sanitisation

- Stored content is safer by default.
- Preview and published rendering use the same cleaned HTML.
- Future WYSIWYG output has a security boundary before it reaches the database.

## Scope

The HTML sanitiser is for CMS body HTML only. It is not for OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data, customer-private values, session IDs or CSRF tokens.
