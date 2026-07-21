# Local media derivative foundation

Phase 1.37n.1 adds the local filesystem foundation for future media derivative processors.

## What this phase does

```text
- resolves deterministic private derivative paths
- exposes matching public derivative URLs
- rejects traversal and absolute source paths
- writes already-produced derivative bytes safely
```

## What this phase does not do

This phase does not resize images yet. It keeps processing engines optional and prepares a safe path/writer layer that future GD or Imagick processors can use behind `MediaProcessorInterface`.

## Path convention

```text
storage/media/derivatives/<profile>/<hash-prefix>/<hash-prefix>/<hash>.<ext>
/media/derivatives/<profile>/<hash-prefix>/<hash-prefix>/<hash>.<ext>
```

## Why this helps package split

A future package such as `zoosper/media-gd` or `zoosper/media-imagick` can focus on image engine work and reuse this local path/writer convention from the base media package.
