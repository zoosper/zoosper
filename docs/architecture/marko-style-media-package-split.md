# Marko-style media package split inspiration

The Marko media packages are useful architectural inspiration for a Zoosper package ecosystem.

## Inspiration pattern

```text
marko/media
marko/media-gd
marko/media-imagick
```

Zoosper can follow the same concept with native contracts:

```text
zoosper/media
zoosper/media-gd
zoosper/media-imagick
```

## Recommended Zoosper interpretation

```text
zoosper/media         owns upload, storage metadata, validation contracts and Editor.js integration.
zoosper/media-gd      future optional GD processor package.
zoosper/media-imagick future optional Imagick processor package.
```

The base package should work without an image processor installed. Processor packages should bind a processor implementation to `MediaProcessorInterface`.

## Guardrail

Use Marko as architecture inspiration, not as source-code copy. Any direct reuse would need licence review and API compatibility review.
