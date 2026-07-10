<?php
/**
 * Page store-view tag selector wrapper.
 *
 * This wrapper keeps page-specific wording out of the generic tag-selector
 * component. It expects the shared `components/form/tag-selector.php` partial to
 * exist from Phase 0.28.
 *
 * @var callable $partial
 * @var list<array{id:int|string,label:string,description?:string}> $siteOptions
 * @var list<int|string> $selectedSiteIds
 */
?>
<?= $partial('components/form/tag-selector.php', [
    'name' => 'site_ids',
    'label' => 'Websites / Store views',
    'options' => $siteOptions ?? [],
    'selected' => $selectedSiteIds ?? [],
    'help' => 'Select one or more websites/store views where this page should be available.',
]) ?>
