# Admin Grid Phase 4K asset-manifest consolidation

The Admin Grid package shipped status, saved-view action, live-layout and page-size
assets, but its canonical `config/admin_assets.php` registered only the base and
compact pairs. Separate patch notes described the missing manifest additions.

Phase 4K promotes the complete ordered asset set into the canonical manifest:

1. base workspace;
2. saved/dirty status;
3. saved-view actions;
4. live responsive layout;
5. compact presentation;
6. base interaction;
7. saved-view actions;
8. page-size interaction;
9. compact interaction.

All scripts remain deferred. The regression test verifies exact ordering, unique
paths and that every declared asset exists in the package.
