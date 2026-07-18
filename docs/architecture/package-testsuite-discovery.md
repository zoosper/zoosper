# Package testsuite discovery

After modules move from `app/` to `packages/` or `vendor/`, root test discovery must follow them.

Phase 1.37h removed the `app/zoosper-media` compatibility path. The package still has tests, but root Pest/PHPUnit configuration may only scan `app/*/tests`. Phase 1.37h.1 adds `packages/*/tests` to root test discovery so extracted modules remain covered by the full verification suite.
