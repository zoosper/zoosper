# Phase 1.37n.4.1 — Local copy processor interface hotfix

The controlled derivative smoke exposed that `LocalCopyMediaProcessor` did not match `MediaProcessorInterface`.

The interface requires:

```php
process(MediaAsset $asset, MediaDerivativePlan $plan): MediaProcessingResult
```

The processor used a transitional string storage path signature. This phase makes the processor interface-compatible and keeps a separate `processStoragePath()` helper for package-local smoke tools and transitional seam wiring.

## Outcome

```text
- LocalCopyMediaProcessor now implements the exact MediaProcessorInterface signature.
- MediaUploadDerivativeDispatcher supports MediaAsset input and transitional storage-path input.
- Smoke tool continues to use the controlled storage-path fixture.
- Regression test locks the interface signature.
```
