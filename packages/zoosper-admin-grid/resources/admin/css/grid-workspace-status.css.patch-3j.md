# Phase 3J toolbar status alignment

Append this responsive rule to the existing workspace-status stylesheet:

```css
.grid-workspace__view-status {
    margin-right: auto;
}

@media (max-width: 48rem) {
    .grid-workspace__view-status {
        flex: 1 1 100%;
    }
}
```

This keeps the active view at the leading edge while the action controls wrap
cleanly on narrow admin screens.
