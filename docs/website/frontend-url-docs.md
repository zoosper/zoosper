# Frontend URL documentation seed

The future Zoosper documentation website should explain:

```text
$cdn->dynamicForContext('/path', $siteContext)
$cdn->staticAsset('/themes/default/assets/css/app.css')
$cdn->media('/library/image.jpg')
```

It should also explain that store-view context is resolved automatically from host/path and that templates should not hard-code store codes.
