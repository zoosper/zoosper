<?php
/**
 * @var callable $e
 * @var list<\Zoosper\Media\Model\MediaAsset> $assets
 * @var string $uploadUrl
 * @var string $csrfToken
 * @var callable $adminUrl
 */
?>
<div class="toolbar">
    <a class="button" href="<?= $e($uploadUrl) ?>">Upload media</a>
</div>

<section class="card">
    <h2>Media library</h2>
    <p class="muted">Only validated image assets are published under <code>/media</code>. Originals remain under private storage.</p>
    <table>
        <thead><tr><th>ID</th><th>Preview</th><th>Filename</th><th>MIME</th><th>Size</th><th>Created</th><th>Lifecycle</th></tr></thead>
        <tbody>
        <?php if (($assets ?? []) === []): ?>
            <tr><td colspan="7">No media assets uploaded yet.</td></tr>
        <?php else: ?>
            <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><?= (int) $asset->id ?></td>
                    <td><?php if ($asset->publicPath !== null): ?><img src="<?= $e($asset->publicPath) ?>" alt="" style="max-width:80px;max-height:60px"><?php endif; ?></td>
                    <td><code><?= $e($asset->filename) ?></code><br><span class="muted"><?= $e($asset->originalFilename) ?></span></td>
                    <td><?= $e($asset->mimeType) ?></td>
                    <td><?= (int) $asset->sizeBytes ?> bytes</td>
                    <td><?= $e((string) ($asset->createdAt ?? '')) ?></td>
                    <td>
                    <?php if ($asset->status === 'archived'): ?>
                        <form method="post" action="<?= $e($adminUrl('media/' . $asset->id . '/restore')) ?>"><input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>"><button type="submit" class="button secondary">Restore</button></form>
                        <form method="post" action="<?= $e($adminUrl('media/' . $asset->id . '/delete')) ?>"><input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>"><p class="muted">Permanent deletion removes the database record and owned files.</p><button type="submit" class="button button--danger">Delete permanently</button></form>
                    <?php else: ?>
                        <form method="post" action="<?= $e($adminUrl('media/' . $asset->id . '/archive')) ?>"><input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>"><button type="submit" class="button secondary">Archive</button></form>
                    <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>
