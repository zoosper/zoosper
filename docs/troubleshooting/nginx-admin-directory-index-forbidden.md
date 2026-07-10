# Nginx `/admin/` Directory Index Forbidden

## Symptom

Access log:

```text
GET /admin/login 302
GET /admin/ 403
```

Error log:

```text
directory index of "/home/vagrant/zoosper/public/admin/" is forbidden
```

## Cause

`public/admin/` exists because Zoosper now serves admin static assets from paths like:

```text
/admin/css/...
/admin/js/...
```

When the browser requests `/admin/`, Nginx sees the real directory `public/admin/` and tries to serve a directory index. Directory listings are disabled, so Nginx returns 403 before Zoosper's PHP router receives the request.

## Correct fix

Do not enable `autoindex`. Instead, make static admin asset paths explicit and route admin application URLs back to the PHP front controller.

Use the example config:

```text
deploy/nginx/zoosper-admin-routing-example.conf
```

Important idea:

```nginx
location ^~ /admin/css/ { try_files $uri =404; }
location ^~ /admin/js/  { try_files $uri =404; }
location = /admin       { try_files /index.php =404; }
location = /admin/      { try_files /index.php =404; }
location ^~ /admin/     { try_files /index.php =404; }
```

## Alternative future improvement

Move static assets away from `/admin/*` to something like:

```text
/assets/admin/css/...
/assets/admin/js/...
```

That will make future dynamic admin paths easier because `/admin` can remain purely an application route.
