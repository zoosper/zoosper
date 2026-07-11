# CDN URL configuration

## Environment variables

```env
CDN_ENABLED=true
CDN_DYNAMIC_BASE_URL=https://www.example.com
CDN_MEDIA_BASE_URL=https://media.example.com
CDN_STATIC_BASE_URL=https://static.example.com
CDN_MEDIA_PATH_PREFIX=/media
CDN_STATIC_PATH_PREFIX=/static
```

## Store-view dynamic URLs

Use JSON for per-store dynamic bases:

```env
CDN_DYNAMIC_STORE_BASE_URLS_JSON={"default":"https://www.example.com","outlet":"https://outlet.example.com"}
```

## Diagnostics

```bash
php tools/diagnose-cdn-config.php
php tools/verify-cdn-url-resolver.php
```

## Security

Do not put credentials or signed private URLs in CDN config. Keep these as public base URLs only.
