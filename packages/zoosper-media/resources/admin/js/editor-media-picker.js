(() => {
    'use strict';

    const PAGE_SIZE = 20;
    const validItem = (item) => item && typeof item.url === 'string' && item.url.startsWith('/media/')
        && typeof item.thumbnail_url === 'string' && typeof item.original_filename === 'string';

    const libraryUrl = (wrapper) => {
        const raw = wrapper.getAttribute('data-zoosper-image-tool');
        if (!raw) return null;
        try {
            const config = JSON.parse(raw);
            const upload = config?.endpoints?.byFile;
            return typeof upload === 'string' && upload.endsWith('/upload') ? upload.slice(0, -7) + '/library' : null;
        } catch (_) {
            return null;
        }
    };

    const element = (name, className, text) => {
        const node = document.createElement(name);
        if (className) node.className = className;
        if (text) node.textContent = text;
        return node;
    };

    const enhance = (wrapper) => {
        const endpoint = libraryUrl(wrapper);
        const toolbar = wrapper.querySelector('.zoosper-content-editor__toolbar');
        if (!endpoint || !toolbar || wrapper.querySelector('[data-media-picker-open]')) return;

        const opener = element('button', 'button button--secondary zoosper-media-picker__open', 'Choose from Media');
        opener.type = 'button';
        opener.dataset.mediaPickerOpen = '';
        opener.disabled = true;
        toolbar.append(opener);

        const dialog = element('dialog', 'zoosper-media-picker');
        dialog.setAttribute('aria-labelledby', `${wrapper.querySelector('.zoosper-content-editor__holder')?.id || 'editor'}-media-title`);
        dialog.innerHTML = '<div class="zoosper-media-picker__surface"><header><h2>Choose an image</h2><button type="button" class="zoosper-media-picker__close" aria-label="Close Media picker">×</button></header><form method="dialog" class="zoosper-media-picker__search"><label>Search images <input type="search" name="q" maxlength="200" autocomplete="off"></label><button type="submit" class="button button--secondary">Search</button></form><p class="zoosper-media-picker__status" role="status" aria-live="polite"></p><div class="zoosper-media-picker__items"></div><nav class="zoosper-media-picker__pagination" aria-label="Media pages"><button type="button" data-page="previous">Previous</button><span></span><button type="button" data-page="next">Next</button></nav></div>';
        dialog.querySelector('h2').id = dialog.getAttribute('aria-labelledby');
        wrapper.append(dialog);

        const status = dialog.querySelector('.zoosper-media-picker__status');
        const items = dialog.querySelector('.zoosper-media-picker__items');
        const previous = dialog.querySelector('[data-page="previous"]');
        const next = dialog.querySelector('[data-page="next"]');
        const pageLabel = dialog.querySelector('.zoosper-media-picker__pagination span');
        const search = dialog.querySelector('input[name="q"]');
        let page = 1;
        let abort = null;

        const close = () => {
            abort?.abort();
            if (dialog.open) dialog.close();
            opener.focus();
        };

        const load = async (requestedPage = 1) => {
            abort?.abort();
            abort = new AbortController();
            status.textContent = 'Loading images…';
            items.replaceChildren();
            previous.disabled = true;
            next.disabled = true;
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('page', String(requestedPage));
            url.searchParams.set('page_size', String(PAGE_SIZE));
            if (search.value.trim()) url.searchParams.set('q', search.value.trim());
            try {
                const response = await fetch(url, {headers: {'Accept': 'application/json'}, credentials: 'same-origin', signal: abort.signal});
                if (!response.ok) throw new Error('request failed');
                const payload = await response.json();
                if (!Array.isArray(payload.items) || !payload.pagination) throw new Error('invalid response');
                const safeItems = payload.items.filter(validItem);
                safeItems.forEach((item) => {
                    const button = element('button', 'zoosper-media-picker__item');
                    button.type = 'button';
                    const image = element('img');
                    image.src = item.thumbnail_url;
                    image.alt = '';
                    image.loading = 'lazy';
                    button.append(image, element('span', '', item.original_filename));
                    button.addEventListener('click', async () => {
                        button.disabled = true;
                        status.textContent = 'Inserting image…';
                        try {
                            await window.ZoosperEditorBridge.insert(wrapper, 'image', {file: {url: item.url}, caption: '', withBorder: false, withBackground: false, stretched: false});
                            close();
                        } catch (_) {
                            button.disabled = false;
                            status.textContent = 'The image could not be inserted.';
                        }
                    });
                    items.append(button);
                });
                page = Number(payload.pagination.page) || 1;
                previous.disabled = !payload.pagination.has_previous;
                next.disabled = !payload.pagination.has_next;
                pageLabel.textContent = `Page ${page} of ${Number(payload.pagination.page_count) || 1}`;
                status.textContent = safeItems.length ? `${Number(payload.pagination.total) || safeItems.length} images available.` : 'No images matched your search.';
            } catch (error) {
                if (error.name !== 'AbortError') status.textContent = 'Media images could not be loaded.';
            }
        };

        wrapper.addEventListener('zoosper:editor-ready', () => { opener.disabled = false; });
        if (wrapper.classList.contains('is-editorjs-ready')) opener.disabled = false;
        opener.addEventListener('click', () => { dialog.showModal(); load(1); search.focus(); });
        dialog.querySelector('.zoosper-media-picker__close').addEventListener('click', close);
        dialog.addEventListener('cancel', (event) => { event.preventDefault(); close(); });
        dialog.querySelector('form').addEventListener('submit', (event) => { event.preventDefault(); load(1); });
        previous.addEventListener('click', () => load(Math.max(1, page - 1)));
        next.addEventListener('click', () => load(page + 1));
    };

    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-zoosper-editor="editorjs"]').forEach(enhance));
})();
