# GridHtmlRenderer column hooks

In `renderTable()`, render stable column markers on every header and cell.

Replace the non-sortable header return with:

```php
return '<th data-grid-column="' . $this->e($column->key) . '">' . $label . '</th>';
```

Add the same attribute to sortable `<th>` output:

```php
<th data-grid-column="..." class="grid-sortable...">
```

In the body loop, change the `<td>` prefix to:

```php
$body .= '<td data-grid-column="' . $this->e($column->key) . '"' . $align . '>';
```

These hooks let the compact panel provide immediate visual previews before the
GET form is submitted, while the server remains authoritative after submission.
