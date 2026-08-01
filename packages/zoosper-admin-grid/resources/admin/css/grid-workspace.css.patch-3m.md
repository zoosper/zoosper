# Phase 3M page-size styling

Append:

```css
.grid-workspace__page-size {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    margin-left: auto;
    font-size: .875rem;
    font-weight: 600;
}

.grid-workspace__page-size select {
    min-width: 5rem;
}

@media (max-width: 48rem) {
    .grid-workspace__page-size {
        width: 100%;
        margin-left: 0;
    }
}
```
