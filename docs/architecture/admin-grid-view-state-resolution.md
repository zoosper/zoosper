# Admin grid view-state resolution

`GridViewStateResolver` combines a user's saved visible-column preference,
named/default bookmark and current query overrides into one validated
`GridViewState`.

Resolution is explicitly scoped by `adminUserId` and `gridKey`. The bookmark
repository never returns another user's state, and an explicit bookmark ID is
accepted only when it exists in that already-scoped result set. Query overrides
are merged after the bookmark and then validated by `GridStateNormaliser`.

The resolved state contains the visible `GridDefinition`, shared `GridCriteria`,
normalised visible-column keys, the current user's bookmarks and the active
bookmark ID. Controllers can now consume one service rather than duplicating
preference/bookmark precedence rules on Pages, Audit Log and Login History.
