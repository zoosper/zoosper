# Secure Session Cookie Default

## Rule

The session cookie `secure` flag defaults to the request scheme:

- HTTPS request  -> secure by default.
- HTTP request   -> not secure (keeps local dev working).
- `SESSION_SECURE` env, when set, always overrides.

## Why

Previously the default was hard-coded `false`, so a production HTTPS deploy that
forgot to set `SESSION_SECURE=true` would transmit the session cookie over plain
HTTP (session hijacking risk). Deriving the default from the request removes that
footgun while keeping local HTTP development frictionless.

## HTTPS detection

`Application::requestIsHttps()` returns true when any of:

- `$_SERVER['HTTPS']` is set and not `off`;
- `$_SERVER['HTTP_X_FORWARDED_PROTO']` is `https` (reverse proxy / load balancer);
- `$_SERVER['SERVER_PORT']` is 443.

If you terminate TLS at a proxy, ensure it forwards `X-Forwarded-Proto: https`.
