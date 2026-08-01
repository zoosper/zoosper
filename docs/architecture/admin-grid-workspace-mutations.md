# Admin Grid workspace mutations

`GridViewMutationService` is the package-level application service for column
preferences and named views. It always requires explicit `adminUserId`,
`gridKey` and the live `GridDefinition`, then normalises submitted state before
persistence.

The service supports:

- save/reset visible-column preference;
- save or update a named view;
- select one default view through the repository's transaction;
- delete a view scoped by user and grid.

ID and Actions stay visible because non-toggleable columns are restored by
`GridStateNormaliser`. Unknown, retired and duplicate columns are removed.
Filters and sort keys are constrained by the live definition, and page size is
bounded.

Security remains deliberately layered. Feature controllers must obtain the user
ID from the authenticated session, use a fixed server-side grid key and
definition, verify permission, and validate CSRF before invoking the mutation
service. No generic endpoint should accept a client-selected repository, class,
user ID or unrestricted grid key.

The next Pages pilot can map the stable action names in
`GridWorkspaceMutationContract` onto feature-owned POST routes and then reuse the
same controller pattern for Audit Log and Login History.
