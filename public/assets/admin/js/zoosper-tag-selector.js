/**
 * Progressive tag selector for Zoosper admin forms.
 *
 * The original checkbox inputs remain the source of truth for form submission.
 * This script only renders selected checkboxes as removable tags and filters
 * available options. It intentionally does not process sensitive values such as
 * OTPs, TOTP secrets, recovery codes, payment data or session identifiers.
 */
(function () {
    'use strict';

    function escapeText(value) {
        return String(value).replace(/[&<>'"]/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char];
        });
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }

        return String(value).replace(/"/g, '\\"');
    }

    function optionLabel(checkbox) {
        var label = checkbox.closest('[data-tag-option]');
        if (!label) {
            return checkbox.value;
        }

        var span = label.querySelector('span');
        return span ? span.textContent.trim() : checkbox.value;
    }

    function refreshSelected(root) {
        var selected = root.querySelector('[data-tag-selected]');
        if (!selected) {
            return;
        }

        var checked = Array.prototype.slice.call(root.querySelectorAll('[data-tag-checkbox]:checked'));
        selected.innerHTML = '';

        checked.forEach(function (checkbox) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'zoosper-tag-selector__tag';
            button.setAttribute('data-tag-remove', checkbox.value);
            button.innerHTML = '<span>' + escapeText(optionLabel(checkbox)) + '</span><strong aria-hidden="true">×</strong>';
            selected.appendChild(button);
        });
    }

    function filterOptions(root, query) {
        var normalised = String(query || '').trim().toLowerCase();
        Array.prototype.forEach.call(root.querySelectorAll('[data-tag-option]'), function (option) {
            var label = option.getAttribute('data-label') || option.textContent.toLowerCase();
            option.hidden = normalised !== '' && label.indexOf(normalised) === -1;
        });
    }

    function init(root) {
        refreshSelected(root);

        root.addEventListener('change', function (event) {
            if (event.target && event.target.matches('[data-tag-checkbox]')) {
                refreshSelected(root);
            }
        });

        root.addEventListener('click', function (event) {
            var remove = event.target.closest('[data-tag-remove]');
            if (!remove) {
                return;
            }

            var value = remove.getAttribute('data-tag-remove');
            var checkbox = root.querySelector('[data-tag-checkbox][value="' + cssEscape(value) + '"]');
            if (checkbox) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        var search = root.querySelector('[data-tag-search]');
        if (search) {
            search.addEventListener('input', function () {
                filterOptions(root, search.value);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-zoosper-tag-selector]'), init);
    });
}());
