# Asset build pipeline plan

## Decision

Module and theme source assets remain outside the web root. A deterministic build
step discovers allowlisted asset manifests and emits immutable, content-hashed
artefacts under `public/assets/build/`. Runtime HTML resolves logical asset IDs
through a generated manifest. Production never serves module source directories.

## Source ownership

```text
app/*/resources/assets/
packages/*/resources/assets/
themes/*/assets/
```

A module declares logical entries in `config/admin_assets.php` or its frontend
asset manifest. Manifests must use logical IDs and repository-relative source
paths. Public URLs are build outputs, never hand-authored source paths.

## Build output

```text
public/assets/build/admin.<hash>.css
public/assets/build/admin.<hash>.js
public/assets/build/theme-default.<hash>.css
public/assets/build/theme-default.<hash>.js
public/assets/build/manifest.json
```

The compiler writes to a temporary directory, validates every output, and then
atomically swaps the completed build into place. Failed builds leave the previous
published generation untouched.

## Security requirements

- Resolve source paths with `realpath()` and require them to remain inside an
  allowlisted module/theme asset root.
- Reject symlinks, traversal segments, absolute paths, executable extensions,
  PHP files, dotfiles, source maps in production, and unknown MIME types.
- Never accept asset paths from HTTP input.
- Copy only declared files and transitive imports discovered by the bundler.
- Keep secrets, configuration, templates, tests, documentation, Composer files,
  and source-control metadata outside public output.
- Serve the public build directory as static, read-only content with directory
  listing disabled and script execution disabled.
- Use content hashes, immutable cache headers and a generated manifest.
- Generate the manifest from the build, not from request data.
- Emit a strict Content Security Policy compatible with external CSS/JS files;
  eliminate inline scripts/styles or use explicit nonces where unavoidable.
- Produce a build inventory with SHA-256 hashes for deployment verification.
- Keep the previous generation for atomic rollback; garbage-collect older
  generations only after successful activation.

## Development and production

Development may render individual module assets for debugging, but must use the
same discovery and path-validation rules. Production always renders compiled,
minified, hashed bundles from the manifest.

## Suggested CLI contract

```text
bin/zoosper assets:build --env=production
bin/zoosper assets:verify
bin/zoosper assets:clean --keep=2
```

`bin/zoosper compile` should call `assets:build` only after module discovery is
successful. Asset building should remain a dedicated service and command so it is
testable independently and is not hidden inside unrelated module compilation.

## Rollout order

1. Stabilise the current Grid and freeze asset names.
2. Add manifest discovery and path validation with no bundling.
3. Add a deterministic copy compiler and runtime manifest resolver.
4. Add CSS/JS minification and content hashing.
5. Add bundle grouping for admin, each frontend theme, and optional lazy entries.
6. Add atomic deployment, verification, rollback and cleanup.
7. Remove legacy direct asset URL generation after parity tests pass.
