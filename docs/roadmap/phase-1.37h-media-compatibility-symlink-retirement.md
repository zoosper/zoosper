# Phase 1.37h — Media compatibility symlink retirement

## Goal

Prove the media module no longer needs an `app/zoosper-media` compatibility path after moving to the package path.

## Scope

- Add a conservative removal tool for the `app/zoosper-media` symlink.
- Add a verifier that requires media discovery from `packages` or `vendor`.
- Add regression tests for the tool behaviour.
- Document rollback steps.

## Out of scope

- Moving any other module.
- Publishing `zoosper/media` to a remote Composer repository.
- Removing root path repository support.
- Editor.js media integration.

## Next phase

Once verified, proceed to Editor.js image block integration backed by media assets.
