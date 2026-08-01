# Auth Grid live bulk cutover

Phase 4V cuts both read-only list actions over to the shared Grid stack in one guarded
build. Existing create, edit, password, 2FA, role-assignment and permission-assignment
methods remain untouched. Both controllers retain their legacy index bodies as a
constructor-null fallback, while the active controller factory injects the new index
façades.

The installer uses PHP tokenisation to replace only `index()`, validates controller
syntax before atomic activation, and is idempotent.
