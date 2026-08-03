# Grid toolbar ownership correction

The View selector now sits in the primary command row beside Filters, Columns and Export. The secondary state row is reserved for status and page-size controls until page-jump and bulk-selection contracts are implemented.

The command-bar script no longer binds Filters or Columns; the established compact-workspace script remains their only owner. This prevents duplicate click handlers from toggling a panel open and immediately closed. Pages that do not render saved-view mutation forms hide the Manage saved views trigger instead of exposing an inert button.

The saved-view select now has a stable ID and name, resolving the browser field-identity warning. The reported CSP warning points to Editor.js and is separate from Grid command-bar behaviour; this build does not weaken CSP.
