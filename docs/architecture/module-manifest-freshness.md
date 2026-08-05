# Module manifest freshness

Compiled module manifests carry separate Composer lock and first-party descriptor stamps. The first-party stamp hashes the sorted relative paths and mtimes of `app/*/module.php`, `modules/*/module.php`, and `modules/*/*/module.php`. A mismatch rejects the compiled manifest and falls back to live discovery. Atomic replacement and cache clearing invalidate OPcache when available.
