# Protected Grid view actions

Phase 3L connects contextual controls to the existing CSRF-bearing mutation forms through stable form IDs. It adds no endpoint and does not duplicate mutation logic.

The visible view-name input is presentation state. Before Save or Make default, the module asset copies its trimmed value into the canonical hidden `view_name` field in the selected protected form. Empty values use browser constraint feedback and remain subject to server validation.

Delete submits the existing form containing the user-scoped bookmark ID. Missing protected forms disable their controls. The script performs no fetch, accepts no user or Grid identity, and remains compatible with a strict CSP.
