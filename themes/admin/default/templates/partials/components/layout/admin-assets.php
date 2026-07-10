<?php
/**
 * Render module-owned admin assets.
 *
 * @var callable $e
 * @var list<\Zoosper\Admin\Asset\AdminAsset> $stylesheets
 * @var list<\Zoosper\Admin\Asset\AdminAsset> $scripts
 */
?>
<?php foreach (($stylesheets ?? []) as $asset): ?>
    <link rel="stylesheet" href="<?= $e($asset->path) ?>">
<?php endforeach; ?>

<?php foreach (($scripts ?? []) as $asset): ?>
    <script src="<?= $e($asset->path) ?>"<?= $asset->defer ? ' defer' : '' ?>></script>
<?php endforeach; ?>
