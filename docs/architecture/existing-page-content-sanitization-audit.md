# Phase 0.65 - Existing page content sanitisation audit

Phase 0.64 sanitises newly saved page content. Phase 0.65 audits and repairs older content that was stored before save-time sanitisation existed.

## Components

```text
PageContentSanitizationResult
PageContentSanitizationAuditor
```

## Design

The auditor compares stored HTML with the configured HTML sanitiser output.

It records only metadata:

```text
row ID
before/after lengths
before/after hashes
pattern names
```

It does not print full CMS body content by default.
