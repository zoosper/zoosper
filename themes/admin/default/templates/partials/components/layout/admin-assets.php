<?php
/**
 * Render module-owned admin assets.
 *
 * The layout should call this partial in the document head for stylesheets and
 * near the end of the body for scripts, or pass both collections here and let
 * the partial render them together. Asset definitions must be static module
 * config and must not include runtime secrets.
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
