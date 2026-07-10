# 2FA enrolment testing

After routes are wired:

```text
GET  /admin/2fa/setup
POST /admin/2fa/setup
```

Test steps:

1. Open setup screen.
2. Add setup key/URI to authenticator.
3. Submit OTP.
4. Confirm recovery codes are shown once.
5. Confirm database stores ciphertext/hash values only.

Never paste real secrets into logs or tickets.
