# Marko media inspiration notes

The Marko media packages are useful references for Zoosper's direction, but Zoosper should not directly copy them without licence and architecture review.

## Useful ideas to borrow conceptually

```text
- A base media package owns common contracts and upload/media management.
- GD and Imagick processors are separate optional packages.
- Callers depend on an image processor interface, not a concrete driver.
- GD can be the simple local driver.
- Imagick can be the advanced production driver.
```

## Zoosper alignment

Zoosper already introduced `MediaProcessorInterface`, `MediaProcessingPolicy`, derivative profiles, and `zoosper/media` package readiness. That maps well to the Marko-style split:

```text
zoosper/media
zoosper/media-gd
zoosper/media-imagick
```

## Guardrails

```text
- Do not vendor-copy Marko source code into Zoosper without review.
- Keep Zoosper contracts compatible with Zoosper's own module system.
- Keep originals immutable.
- Treat GD and Imagick as optional driver packages.
- Keep browser upload working even when no processor is installed.
```
