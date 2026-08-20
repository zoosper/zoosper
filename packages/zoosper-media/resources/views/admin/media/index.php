<?php
declare(strict_types=1);
/** @var string $gridHtml @var string $uploadUrl */
?>
<section class="card">
    <div class="admin-page-heading"><h2>Media library</h2><a class="button" href="<?= htmlspecialchars($uploadUrl, ENT_QUOTES, 'UTF-8') ?>">Upload media</a></div>
    <?= $gridHtml ?>
</section>
