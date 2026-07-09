<?php
/**
 * @var callable $e
 * @var callable $partial
 * @var \Zoosper\Page\Model\Page|null $page
 * @var \Zoosper\Site\Model\Site $site
 * @var string $content
 * @var string $versionLabel
 */
$title = isset($page) ? ($page->metaTitle ?? $page->title) : $site->name;
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
    <?= $partial('header.php') ?>
    <?= $content ?>
    <?= $partial('footer.php') ?>
</body>
</html>
