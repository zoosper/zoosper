<?php
/** @var callable $e */
/** @var array<string, list<\Zoosper\Settings\Definition\SettingsSection>> $categories */
/** @var int $sectionCount */
/** @var array<string, \Zoosper\Settings\Value\SettingValue> $effectiveValues */
/** @var string $scopeLabel */
/** @var string $scopeType */
/** @var string $scopeKey */
/** @var list<string> $websiteOptions */
/** @var list<string> $storeOptions */
/** @var array<int, object> $siteOptions */
/** @var string $csrfToken */
/** @var bool $showPaths */
?>
<style>
.settings-head{display:flex;justify-content:space-between;gap:1rem;align-items:flex-end;margin-bottom:1rem}.settings-search{width:min(30rem,100%);padding:.72rem .85rem;border:1px solid #cbd5e1;border-radius:.65rem;background:#fff}.settings-summary{color:#64748b;font-size:.9rem}.settings-category{margin:1.25rem 0}.settings-category h2{font-size:1rem;text-transform:capitalize;margin:0 0 .65rem}.settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr));gap:.8rem}.settings-card{border:1px solid #e2e8f0;border-radius:.8rem;padding:1rem;background:#fff}.settings-card h3{margin:0 0 .35rem;font-size:1rem}.settings-meta{display:flex;gap:.4rem;flex-wrap:wrap;margin:.65rem 0}.settings-badge{font-size:.72rem;padding:.2rem .45rem;border-radius:999px;background:#eef2ff;color:#3730a3}.settings-field{border-top:1px solid #f1f5f9;padding:.55rem 0}.settings-field:first-of-type{border-top:0}.settings-field__row{display:flex;justify-content:space-between;gap:.75rem}.settings-empty{padding:2rem;text-align:center;color:#64748b}.settings-hidden{display:none!important}.settings-scope{display:flex;gap:.5rem;flex-wrap:wrap;align-items:end;margin:0 0 1rem}.settings-scope label{display:grid;gap:.25rem;font-size:.8rem;color:#475569}.settings-scope select{padding:.55rem .7rem;border:1px solid #cbd5e1;border-radius:.55rem;background:#fff}.settings-scope button,.settings-save{padding:.58rem .85rem;border:0;border-radius:.55rem;background:#1d4ed8;color:#fff}.settings-input{width:100%;max-width:34rem;padding:.6rem .7rem;border:1px solid #cbd5e1;border-radius:.55rem}.settings-edit{margin-top:.7rem;padding-top:.7rem;border-top:1px solid #e2e8f0}
</style>
<div class="settings-head">
    <div>
        <h1>Settings</h1>
        <p class="settings-summary"><?= $e((string) $sectionCount) ?> module-owned section<?= $sectionCount === 1 ? '' : 's' ?>. Read-only catalogue. Current scope: <strong><?= $e($scopeLabel) ?></strong>.</p>
    </div>
    <label>
        <span class="sr-only">Search settings</span>
        <input id="settings-search" class="settings-search" type="search" placeholder="Search settings, modules and paths" autocomplete="off">
    </label>
</div>
<form class="settings-scope" method="get" action="/admin/settings">
    <label>Scope
        <select name="scope" id="settings-scope-type">
            <?php foreach (['default' => 'Default', 'website' => 'Website', 'store' => 'Store', 'site' => 'Site'] as $value => $label): ?>
                <option value="<?= $e($value) ?>" <?= $scopeType === $value ? 'selected' : '' ?>><?= $e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label id="settings-scope-key-label">Scope value
        <select name="scope_key" id="settings-scope-key" data-selected="<?= $e($scopeKey) ?>"></select>
    </label>
    <button type="submit">View scope</button>
    <a href="/admin/settings">Reset to Default</a>
</form>
<script type="application/json" id="settings-scope-options"><?= json_encode([
    'website' => array_combine($websiteOptions, $websiteOptions) ?: [],
    'store' => array_combine($storeOptions, $storeOptions) ?: [],
    'site' => array_reduce($siteOptions, static function (array $result, object $site): array { $result[(string) $site->id] = $site->name; return $result; }, []),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<div id="settings-empty" class="settings-empty settings-hidden">No settings match this search.</div>
<div id="settings-catalogue">
<?php foreach ($categories as $category => $sections): ?>
    <section class="settings-category" data-settings-category>
        <h2><?= $e($category) ?></h2>
        <div class="settings-grid">
        <?php foreach ($sections as $section): ?>
            <article class="settings-card" data-settings-card data-search="<?= $e(strtolower($section->id . ' ' . $section->label . ' ' . $section->description . ' ' . $section->module . ' ' . implode(' ', array_map(static fn ($setting) => $setting->path . ' ' . $setting->label . ' ' . $setting->description, $section->settings)))) ?>">
                <h3><?= $e($section->label) ?></h3>
                <?php if ($section->description !== ''): ?><p class="muted"><?= $e($section->description) ?></p><?php endif; ?>
                <div class="settings-meta">
                    <span class="settings-badge"><?= $e($section->module) ?></span>
                    <span class="settings-badge"><?= $e((string) count($section->settings)) ?> setting<?= count($section->settings) === 1 ? '' : 's' ?></span>
                </div>
                <?php $editable = array_filter($section->settings, static fn ($setting) => !$setting->readOnly && !$setting->secret && $effectiveValues[$setting->path]->source !== 'project'); ?>
                <?php if ($editable !== []): ?>
                <form class="settings-edit" method="post" action="/admin/settings/save">
                    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="section" value="<?= $e($section->id) ?>">
                    <input type="hidden" name="scope" value="<?= $e($scopeType) ?>">
                    <input type="hidden" name="scope_key" value="<?= $e($scopeKey) ?>">
                <?php endif; ?>
                <?php foreach ($section->settings as $setting): ?>
                    <div class="settings-field">
                        <div class="settings-field__row"><strong><?= $e($setting->label) ?></strong><span class="settings-badge"><?= $e($setting->type) ?></span></div>
                        <?php if ($showPaths): ?><code><?= $e($setting->path) ?></code><?php endif; ?>
                        <?php if ($setting->description !== ''): ?><p class="muted"><?= $e($setting->description) ?></p><?php endif; ?>
                        <?php $effective = $effectiveValues[$setting->path]; ?>
                        <div class="settings-meta">
                            <span class="settings-badge">Source: <?= $e(ucfirst($effective->source)) ?></span>
                            <?php if ($effective->readOnly): ?><span class="settings-badge">Read-only</span><?php endif; ?>
                            <?php if ($effective->secret): ?><span class="settings-badge">Secret</span><?php endif; ?>
                        </div>
                        <div class="settings-field__value">
                            <strong>Effective value</strong><br>
                            <code><?= $effective->secret ? '••••••••' : $e($effective->value === null ? 'Not set' : (is_bool($effective->value) ? ($effective->value ? 'Enabled' : 'Disabled') : (string) $effective->value)) ?></code>
                        </div>
                        <?php if ($effective->explanation !== null): ?><p class="muted"><?= $e($effective->explanation) ?></p><?php endif; ?>
                        <?php if ($effective->source === 'database' && in_array($setting, $editable, true)): ?>
                            <button
                                type="submit"
                                name="path"
                                value="<?= $e($setting->path) ?>"
                                formaction="/admin/settings/clear"
                                formmethod="post"
                            >Use inherited value</button>
                        <?php endif; ?>
                        <?php if (in_array($setting, $editable, true)): ?>
                            <?php $inputName = 'settings[' . $setting->path . ']'; ?>
                            <?php if ($setting->type === 'boolean'): ?>
                                <input type="hidden" name="<?= $e($inputName) ?>" value="0">
                                <label><input type="checkbox" name="<?= $e($inputName) ?>" value="1" <?= in_array((string) $effective->value, ['1', 'true'], true) || $effective->value === true ? 'checked' : '' ?>> Enabled</label>
                            <?php elseif ($setting->type === 'textarea'): ?>
                                <textarea class="settings-input" name="<?= $e($inputName) ?>" rows="4"><?= $e($effective->value === null ? '' : (string) $effective->value) ?></textarea>
                            <?php elseif ($setting->type === 'select'): ?>
                                <select class="settings-input" name="<?= $e($inputName) ?>">
                                    <?php foreach ($setting->options as $option): ?>
                                        <option value="<?= $e($option) ?>" <?= (string) $effective->value === $option ? 'selected' : '' ?>><?= $e($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($setting->type === 'multiselect'): ?>
                                <?php $selectedValues = is_string($effective->value) ? json_decode($effective->value, true) : []; ?>
                                <?php $selectedValues = is_array($selectedValues) ? array_map('strval', $selectedValues) : []; ?>
                                <select class="settings-input" name="<?= $e($inputName) ?>[]" multiple size="<?= $e((string) max(3, min(8, count($setting->options)))) ?>">
                                    <?php foreach ($setting->options as $option): ?>
                                        <option value="<?= $e($option) ?>" <?= in_array($option, $selectedValues, true) ? 'selected' : '' ?>><?= $e($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <?php $inputType = match ($setting->type) { 'email' => 'email', 'url' => 'url', 'integer' => 'number', 'decimal' => 'number', default => 'text' }; ?>
                                <input
                                    class="settings-input"
                                    type="<?= $e($inputType) ?>"
                                    name="<?= $e($inputName) ?>"
                                    value="<?= $e($effective->value === null ? '' : (string) $effective->value) ?>"
                                    <?= $setting->type === 'integer' ? 'step="1"' : ($setting->type === 'decimal' ? 'step="any"' : '') ?>
                                >
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($editable !== []): ?><button class="settings-save" type="submit">Save <?= $e($section->label) ?></button></form><?php endif; ?>
            </article>
        <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
</div>
<script>
(function(){const type=document.getElementById('settings-scope-type');const key=document.getElementById('settings-scope-key');const label=document.getElementById('settings-scope-key-label');const options=JSON.parse(document.getElementById('settings-scope-options').textContent);function rebuild(){const selected=key.dataset.selected;key.innerHTML='';const values=options[type.value]||{};Object.entries(values).forEach(function(entry){const option=new Option(entry[1],entry[0],false,entry[0]===selected);key.add(option);});label.classList.toggle('settings-hidden',type.value==='default');key.disabled=type.value==='default';}type.addEventListener('change',function(){key.dataset.selected='';rebuild();});rebuild();})();
(function(){const input=document.getElementById('settings-search');const empty=document.getElementById('settings-empty');if(!input)return;input.addEventListener('input',function(){const query=input.value.trim().toLowerCase();let visible=0;document.querySelectorAll('[data-settings-card]').forEach(function(card){const show=query===''||card.dataset.search.includes(query);card.classList.toggle('settings-hidden',!show);if(show)visible++;});document.querySelectorAll('[data-settings-category]').forEach(function(category){category.classList.toggle('settings-hidden',!category.querySelector('[data-settings-card]:not(.settings-hidden)'));});empty.classList.toggle('settings-hidden',visible!==0);});})();
</script>
