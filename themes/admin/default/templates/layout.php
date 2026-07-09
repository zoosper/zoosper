<?php
/**
 * @var callable $e
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
<div class="admin-shell">
    <aside class="admin-sidebar"><div class="brand">Zoosper</div><?= $navigation ?></aside>
    <section class="admin-main">
        <header class="admin-topbar"><strong><?= $e($title) ?></strong><span class="muted"><?= $e($userName) ?></span></header>
        <main class="admin-content"><?= $content ?></main>
    </section>
</div>
<footer class="cms-version-footer"><?= $e($version) ?></footer>
</body>
</html>
