# Release promotion to the default branch

This runbook is mandatory after a Zoosper release tag and public GitHub release have been finalised. It prevents the public default branch from remaining on the previous release.

## Branch and tag roles

- `dev` is the supported development branch and may contain work completed after the newest release.
- `master` is the public default branch and must be promoted to the exact immutable commit represented by the newest published release tag.
- Published tags are immutable. Never move, recreate, delete, or amend an existing release tag.
- Do not merge the current `dev` tip into `master` merely to publish a release. Post-release work must remain on `dev`.

## Required sequence

1. Complete release rehearsal and acceptance on the exact release candidate commit.
2. Create and verify the annotated release tag.
3. Publish the GitHub prerelease or release from that exact tag target.
4. Verify the public artifact and checksum.
5. Fetch `dev`, `master`, and all tags without modifying the worktree.
6. Prove all of the following before promotion:
   - local and remote `dev` are aligned and clean;
   - remote `master` is still the expected previous release commit;
   - the new tag object and peeled commit match their recorded hashes;
   - the public GitHub release targets the same peeled commit;
   - `master` is an ancestor of the release commit;
   - the release commit is an ancestor of `dev`;
   - the release commit has no commits behind the current `master` tip.
7. Fast-forward remote `master` directly to the peeled release commit. The push must name the exact commit and `refs/heads/master`.
8. Verify remote `master`, remote `dev`, the previous tag, and the new tag independently after the push.
9. Record the promotion evidence with the before/after branch hashes, tag object hash, peeled commit hash, release metadata, and final remote refs.
10. Check the public default-branch README and release documentation. If they still describe the release as a candidate, prepare a separate documentation-only correction. Do not retag or change immutable release bytes.

## Prohibited shortcuts

- No force push.
- No merge commit for a fast-forwardable release.
- No retagging or tag deletion.
- No merge of post-release `dev` commits into `master`.
- No rebuilding or replacing already published artifacts during branch promotion.
- No manual ref edits after a failed promotion script. Preserve the report and inspect the partial state first.
- No merge from `master` back into `dev` while `master` is already an ancestor of `dev`.

## Reference command shape

The promotion command is intentionally a commit-to-branch push rather than a checkout or merge:

```bash
ZOOSPER_SKIP_PRE_PUSH=1 git push origin "<peeled-release-commit>:refs/heads/master"
```

Use this only after all release, ancestry, protected-ref, and public-release checks above pass. Skipping the pre-push hook is permitted only when the exact release commit has already passed the complete release gates and the promotion script re-verifies immutable identity.

## Alpha.4 recorded example

The `v0.3.2-alpha.4` promotion on 9 October 2026 used these verified identities:

- previous `master`: `4676b012bc579d6f66f20aa317c4da862d5f045a`
- annotated tag object: `930e2c51a1d6f71a0d56649ef9e3f5ea13da5275`
- peeled release commit and promoted `master`: `74db4e87c43999da84e59329dab6ff83d399dbb6`
- post-release `dev` preserved at promotion time: `c08018c5fc88db5b69a2722e576821490c5535a9`

This example is historical evidence, not a reusable set of expected hashes. Every release must obtain and verify its own exact identities.
