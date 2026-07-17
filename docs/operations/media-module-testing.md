# Media module testing

Run:

```bash
php -l app/zoosper-media/src/Controller/MediaAdminController.php
php -l app/zoosper-media/src/Repository/MediaAssetRepository.php
php -l app/zoosper-media/src/Service/MediaUploadValidator.php
php -l app/zoosper-media/src/Service/MediaStorage.php
php bin/zoosper-schema validate
vendor/bin/pest app/zoosper-media/tests
PHP=php8.5 bin/verify
```

Browser checks:

```text
/admin/media
/admin/media/upload
```

Expected:

```text
Media menu appears for media.manage users.
Upload screen requires an image file.
JPG/PNG/GIF/WebP uploads create a media_assets row.
Original file is stored under storage/media/original.
Validated public copy is available under public/media.
Unsupported extensions are rejected before storage.
```
