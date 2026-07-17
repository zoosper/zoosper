<?php
/**
 * @var callable $e
 * @var string $action
 * @var string $csrfToken
 * @var list<string> $errors
 */
?>
<div class="toolbar"><a class="button secondary" href="/admin/media">Back</a></div>

<?php if (($errors ?? []) !== []): ?>
    <div class="notice notice-error">
        <strong>Upload failed.</strong>
        <ul>
            <?php foreach ($errors as $error): ?><li><?= $e($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" action="<?= $e($action) ?>" class="page-form">
    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
    <section class="card">
        <h2>Upload media</h2>
        <p class="muted">Allowed image types: JPG, PNG, GIF and WebP. Maximum size: 5 MB.</p>
        <label>Image file <input type="file" name="media_file" accept="image/jpeg,image/png,image/gif,image/webp" required></label>
    </section>
    <div class="toolbar"><button type="submit">Upload media</button></div>
</form>
