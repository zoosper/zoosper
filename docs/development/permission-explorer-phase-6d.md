# Phase 6D: Permission Explorer public asset publication

The rendered role page included `/asset/zoosper-auth/...` URLs, but the asset resolver did not expose those files and returned Asset not found. Phase 6D publishes the already validated Permission Explorer CSS and JavaScript into the established `public/assets/admin` pipeline and rewrites the permission partial to those public URLs.

The module-owned source files remain canonical. The public files are deployment artefacts copied from those sources by the guarded apply script. A rendered-wiring regression test requires both URLs and both public files.
