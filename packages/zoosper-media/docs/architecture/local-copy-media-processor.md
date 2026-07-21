# Local copy media processor

Phase 1.37n.2 introduces an engine-free `LocalCopyMediaProcessor` behind `MediaProcessorInterface`.

The adapter is deliberately conservative:

```text
- no resize
- no crop
- no re-encode
- no EXIF stripping
- no format conversion
```

It copies an already validated original into deterministic derivative slots produced by the local derivative path resolver. This gives Zoosper a real orchestration target for derivative processing before optional engine packages such as `zoosper/media-gd` or `zoosper/media-imagick` exist.

Original uploads remain immutable. The processor never writes back to the source path.
