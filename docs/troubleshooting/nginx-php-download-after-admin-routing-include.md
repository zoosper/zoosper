# Nginx downloads PHP after admin routing include

## Symptom

After including an admin routing snippet, the browser downloads `index.php` or another PHP response instead of executing it.

## Cause

A location such as this can route to the physical `index.php` file without passing the request through PHP-FPM from that same selected location:

```nginx
location = /admin/ {
    try_files /index.php =404;
}
```

Depending on Nginx location selection and internal redirect behaviour, `index.php` can be treated as a static file. A robust approach is to send application routes to a named FastCGI front-controller location instead.

## Preferred pattern

```nginx
location / {
    try_files $uri @zoosper_front_controller;
}

location @zoosper_front_controller {
    include fastcgi_params;
    fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    fastcgi_param SCRIPT_NAME /index.php;
    fastcgi_param REQUEST_URI $request_uri;
    fastcgi_param QUERY_STRING $query_string;
    fastcgi_index index.php;
}
```

Also avoid `$uri/` in the main front-controller fallback, because a real directory such as `public/admin/` can trigger a directory-index check before the PHP router sees the request.

## Server-name note

The supplied working config used:

```nginx
server_name zooser.example.com.au;
```

but the browser request used:

```text
zoosper.example.com.au
```

Use the correct hostname in the SSL server block to avoid serving the request through an unintended default server.
