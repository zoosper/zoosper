# Atomic module manifest writes

`var/cache/modules.php` is executable PHP required during application boot. The
compiler must never write directly onto the live path because another process
could read a partially written file.

Compilation now writes the complete contents to a temporary file in the same
cache directory, uses an exclusive write lock, verifies the byte count, and
atomically renames the completed file over the live manifest. Same-directory
rename keeps the replacement on one filesystem. Temporary files are removed in
a `finally` block and OPcache is invalidated after replacement or deletion when
the function is available.

The compiled format and `ModuleManifestCompiler` public API are unchanged. Cache
fingerprinting and stale-input detection remain a separate phase because they
require coordinated changes in both the compiler and the current reconciled
`ModuleRegistry` reader.
