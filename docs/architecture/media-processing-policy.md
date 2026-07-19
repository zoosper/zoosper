# Media processing policy and derivative architecture

Phase 1.37n defines the media derivative architecture without doing heavy image processing inside the current upload request path.

## Policy decisions

```text
- Uploaded originals are immutable.
- Originals remain under storage/media/original.
- Derived files are planned under storage/media/derivatives.
- Browser-facing derivative URLs are planned under /media/cache.
- WebP is the default derivative format for generated profiles.
- Queue-backed processing is recommended for the long-term path.
```

## New contracts

```text
Zoosper\Media\Processing\MediaDerivativeProfile
Zoosper\Media\Processing\MediaDerivativePlan
Zoosper\Media\Processing\MediaProcessingPolicy
Zoosper\Media\Processing\MediaProcessorInterface
Zoosper\Media\Processing\MediaProcessingResult
```

## Default derivatives

The default plan defines these profiles:

```text
thumb   320x240   webp   cover
medium  960x720   webp   contain
large   1600x1200 webp   contain
```

## Why no real conversion yet

This phase keeps processing out of the upload request path until the system has an explicit processor implementation, storage strategy and queue direction. That avoids baking GD, Imagick, Redis, RabbitMQ or cloud storage assumptions directly into `MediaStorage` or `MediaEditorJsUploadController`.

## Future implementation direction

A later implementation phase can add a local processor first, then place async dispatch behind the same contract later:

```text
MediaProcessorInterface
  -> LocalImageProcessor / GdImageProcessor / ImagickImageProcessor
  -> future queued processor / worker command
  -> future storage abstraction
```
