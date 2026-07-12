# Translation file aggregator comment safety

Avoid literal wildcard path examples inside PHPDoc when they create a `/*` sequence.

Unsafe inside a docblock:

```text
app/*/i18n/{locale}.php
```

Safe alternative:

```text
application module i18n directories
```

Runtime glob patterns remain valid inside PHP string literals. Only PHPDoc examples needed to be adjusted.
