<?php
/**
 * @var callable $e
 * @var string $csrfToken
 * @var list<\Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncement> $announcements
 * @var array<int, int> $acknowledgmentCounts
 * @var ?\Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncement $editItem
 * @var string $saveUrl
 * @var string $publishUrl
 * @var string $unpublishUrl
 * @var string $archiveUrl
 * @var string $announcementsUrl
 */
$announcements = $announcements ?? [];
$acknowledgmentCounts = $acknowledgmentCounts ?? [];
$editItem = $editItem ?? null;
?>
<div class="announcement-workspace" data-announcement-workspace>
<header class="page-header announcement-workspace__header">
    <div class="page-header__copy">
        <p class="page-header__eyebrow">System / Announcements</p>
        <h1>Global Announcements</h1>
        <p class="muted">Broadcast mandatory notifications to active dashboard users and offline users upon their next login.</p>
    </div>
</header>

<div class="announcement-workspace__layout">
    <section class="card announcement-editor">
        <h2><?= $editItem !== null ? 'Edit Announcement' : 'Draft New Announcement' ?></h2>
        <form method="post" action="<?= $e($saveUrl) ?>" class="admin-form">
            <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
            <?php if ($editItem !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $editItem->id ?>">
            <?php endif; ?>

            <div class="field">
                <label for="announcement-title"><strong>Title</strong></label>
                <input type="text" id="announcement-title" name="title" value="<?= $e($editItem?->title ?? '') ?>" required placeholder="e.g. Scheduled System Maintenance">
            </div>

            <div class="field">
                <label for="announcement-body"><strong>Message Content</strong></label>
                <textarea id="announcement-body" name="body" rows="6" required placeholder="Write the announcement details here..."><?= $e($editItem?->body ?? '') ?></textarea>
            </div>

            <div class="field">
                <label for="announcement-status"><strong>Status</strong></label>
                <select id="announcement-status" name="status">
                    <option value="draft"<?= ($editItem?->status ?? 'draft') === 'draft' ? ' selected' : '' ?>>Draft (offline/not broadcasted)</option>
                    <option value="published"<?= ($editItem?->status ?? '') === 'published' ? ' selected' : '' ?>>Published (broadcast to active &amp; next login)</option>
                    <option value="archived"<?= ($editItem?->status ?? '') === 'archived' ? ' selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div class="actions announcement-editor__actions">
                <button type="submit" class="button button--primary"><?= $editItem !== null ? 'Update Announcement' : 'Save Announcement' ?></button>
                <?php if ($editItem !== null): ?>
                    <a href="<?= $e($announcementsUrl) ?>" class="button button--secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="card announcement-history">
        <div class="announcement-history__header"><div><p class="page-header__eyebrow">Delivery history</p><h2>Announcements History</h2></div><p class="muted"><?= count($announcements) ?> total</p></div>
        <?php if ($announcements === []): ?>
            <div class="admin-empty-state announcement-history__empty">
                <p class="muted">No announcements drafted yet.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-container announcement-history__table-wrap" role="region" aria-label="Announcements history" tabindex="0">
                <table class="admin-table announcement-history__table">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Status</th>
                            <th scope="col">Published</th>
                            <th scope="col" class="announcement-history__ack">Acknowledgments</th>
                            <th scope="col" class="announcement-history__actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $item): ?>
                            <?php $ackCount = $acknowledgmentCounts[$item->id] ?? 0; ?>
                            <tr>
                                <td class="announcement-history__message">
                                    <strong><?= $e($item->title) ?></strong>
                                    <div class="muted announcement-history__preview">
                                        <?= $e(mb_strlen($item->body) > 80 ? mb_substr($item->body, 0, 77) . '...' : $item->body) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($item->isPublished()): ?>
                                        <span class="announcement-status announcement-status--published">Published</span>
                                    <?php elseif ($item->isDraft()): ?>
                                        <span class="announcement-status announcement-status--draft">Draft</span>
                                    <?php else: ?>
                                        <span class="announcement-status announcement-status--archived">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td class="announcement-history__published">
                                    <?= $item->publishedAt !== null ? $e($item->publishedAt->format('Y-m-d H:i')) : '<span class="muted">—</span>' ?>
                                </td>
                                <td class="announcement-history__ack">
                                    <?= (int) $ackCount ?>
                                </td>
                                <td class="announcement-history__actions">
                                    <div class="announcement-history__action-list">
                                        <a href="<?= $e($announcementsUrl . '?id=' . $item->id) ?>" class="button button--sm button--secondary">Edit</a>
                                        <?php if ($item->isPublished()): ?>
                                            <form method="post" action="<?= $e($unpublishUrl) ?>" class="announcement-history__action-form">
                                                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                <button type="submit" class="button button--sm button--secondary">Unpublish</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="<?= $e($publishUrl) ?>" class="announcement-history__action-form">
                                                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                <button type="submit" class="button button--sm announcement-action--publish">Publish</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (!$item->isArchived()): ?>
                                            <form method="post" action="<?= $e($archiveUrl) ?>" class="announcement-history__action-form">
                                                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item->id ?>">
                                                <button type="submit" class="button button--sm button--secondary">Archive</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
</div>
