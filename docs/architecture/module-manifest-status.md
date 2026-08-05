# Module manifest status

Phase 8E exposes Phase 8D runtime diagnostics through a read-only operational status service and the `module:manifest:status` console command.

The status is one of:

- `missing`: no optional compiled manifest exists, so live discovery is active.
- `fresh`: the compiled manifest exists and was accepted.
- `rejected`: the compiled manifest exists but was rejected; the Phase 8D machine-readable reason is included.

The command does not compile, clear, or rewrite the manifest.
