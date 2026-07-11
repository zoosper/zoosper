# Site/store-view context configuration

## Simple default env

```env
DEFAULT_WEBSITE_CODE=main
DEFAULT_STORE_CODE=main
DEFAULT_STORE_VIEW_CODE=default
DEFAULT_LOCALE=en_AU
DEFAULT_CURRENCY=AUD
DEFAULT_STORE_VIEW_DOMAINS=zoosper.lowes.com.au
APP_URL=https://zoosper.lowes.com.au
```

## Multiple store views with JSON

```env
SITE_CONTEXT_JSON={"default_store_view":"au_en","store_views":{"au_en":{"website_code":"lowes_au","website_name":"Lowes AU","store_code":"retail","store_name":"Retail","store_view_code":"au_en","store_view_name":"Australia English","locale":"en_AU","currency":"AUD","base_url":"https://www.lowes.com.au","domains":["www.lowes.com.au"],"path_prefix":"","is_active":true},"nz_en":{"website_code":"lowes_nz","website_name":"Lowes NZ","store_code":"retail","store_name":"Retail","store_view_code":"nz_en","store_view_name":"New Zealand English","locale":"en_NZ","currency":"NZD","base_url":"https://www.lowes.co.nz","domains":["www.lowes.co.nz"],"path_prefix":"","is_active":true}}}
```

## Diagnostics

```bash
php tools/verify-site-context.php
php tools/diagnose-site-context.php --host=zoosper.lowes.com.au --path=/about-us
```

## Security

Do not put credentials, signed private URLs, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values in site context config.
