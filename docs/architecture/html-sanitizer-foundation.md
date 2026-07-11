# Phase 0.63 - HTML Sanitiser / Safe Content Rendering Foundation

Zoosper now has a sanitisation boundary for CMS/WYSIWYG body HTML.

## Components

```text
HtmlSanitizerInterface
SanitizedHtml
BasicHtmlSanitizer
HtmlPurifierSanitizer
HtmlSanitizerFactory
```

## Recommended driver

Use `ezyang/htmlpurifier` for production WYSIWYG/rich HTML content.

## Security rules

Do not process or log sensitive values through the HTML sanitiser:

```text
OTP values
TOTP secrets
recovery-code plaintext
reset tokens
SMTP passwords
payment data
customer-private values
session IDs
CSRF tokens
```

The sanitiser is for CMS body HTML only.
