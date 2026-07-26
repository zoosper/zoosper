# 2FA Verify-and-Consume Domain

Adds the domain operations required by the login-time 2FA challenge controller:
read active protected TOTP secrets, reveal them transiently through `SecretProtector::reveal()`, and redeem recovery codes exactly once by marking `used_at` after `password_verify()` succeeds.
