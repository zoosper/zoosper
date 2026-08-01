# Phase 3L contextual control binding

Change the visible name input to `name="workspace_view_name" data-grid-view-name` so it is presentation-only. Keep `maxlength="120"`.

Map contextual buttons to the stable protected forms using the HTML `form` attribute:

```text
save_view        -> grid-workspace-save-view
set_default_view -> grid-workspace-set-default-view
delete_view      -> grid-workspace-delete-view
```

The module asset copies the trimmed visible name into the selected form's canonical hidden `view_name` field immediately before submission.
