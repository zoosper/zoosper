# Editor.js image frontend rendering

Phase 1.37k wires managed Editor.js `image` blocks into frontend page rendering.

## Rendering path

```text
pages.content_json
  -> Zoosper\Page\Content\BlockJsonToHtmlRenderer
  -> Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer
  -> conservative figure/img HTML
```

## Safety policy

Only managed local media URLs beginning with `/media/` are rendered. Remote image URLs are ignored until a separate remote fetch/proxy policy exists.

The renderer owns the generated HTML and escapes captions, URLs and CSS class output before returning markup to the page templates.

## Output shape

An accepted Editor.js image block renders as:

```html
<figure class="cms-image">
    <img src="/media/..." alt="Caption" loading="lazy">
    <figcaption>Caption</figcaption>
</figure>
```

Optional Editor.js image flags map to conservative CSS classes:

```text
withBorder      -> cms-image--bordered
withBackground  -> cms-image--background
stretched       -> cms-image--stretched
```
