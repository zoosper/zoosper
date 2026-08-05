# Module manifest output format validation

Phase 8H validates `--format` once after option parsing for both manifest commands. Supported values are `text` and `json`. Unsupported values write to STDERR and exit with code `2`; manifest-health failure remains code `1`.
