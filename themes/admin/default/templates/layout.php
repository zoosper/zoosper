<?php
/**
 * @var callable $e
 * @var callable $partial
 * @var callable $slot
 * @var string $title
 * @var string $navigation
 * @var string $content
 * @var string $userName
 * @var string $version
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title) ?> - Zoosper Admin</title>
    <link rel="stylesheet" href="/themes/admin/default/assets/css/admin.css">
</head>
<body>
<?= $slot('body.start') ?>
<div class="admin-shell">
    <aside class="admin-sidebar"><div class="brand">Zoosper</div><?= $navigation ?></aside>
    <section class="admin-main">
        <header class="admin-topbar"><strong><?= $e($title) ?></strong><span class="muted"><?= $e($userName) ?></span></header>
        <?= $slot('before.content') ?>
        <main class="admin-content"><?= $content ?></main>
        <?= $slot('after.content') ?>
    </section>
</div>
<?= $partial('footer.php') ?>
<?= $slot('body.end') ?>
</body>
</html>
