<?php
/**
 * @var callable $e
 * @var \Zoosper\Page\Model\Page $page
 * @var \Zoosper\Site\Model\Site $site
 * @var string $versionLabel
 */
$title = $page->metaTitle ?? $page->title;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title) ?></title>
    <link rel="stylesheet" href="/themes/default/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <strong><?= $e($site->name) ?></strong>
    </header>
    <main class="page-shell">
        <h1><?= $e($page->title) ?></h1>
        <div class="page-content"><?= nl2br($e($page->content)) ?></div>
    </main>
    <footer class="site-footer"><?= $e($versionLabel) ?></footer>
</body>
</html>
