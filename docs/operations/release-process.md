# Release process

Production artifact compilation writes relocatable project-root-relative module, service, and route cache references. The generated caches must not contain temporary workspace paths or wall-clock generation timestamps. Two artifact builds from the same commit must produce identical SHA-256 values before publication.

The POSIX archive deletes volatile PAX `atime` and `ctime` fields so identical release trees from the same source commit remain byte-for-byte reproducible.
