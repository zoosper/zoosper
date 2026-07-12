# Translation aggregator verifier scope

The translation file aggregator must keep wildcard glob patterns in runtime string literals, for example:

```php
$this->basePath . '/app/*/i18n/' . $filename
```

However, wildcard examples must not appear literally inside PHPDoc when they create a slash-star sequence. Therefore the verifier should check only the PHPDoc block for unsafe examples, while separately confirming runtime glob strings remain present.
