# Admin Tag-style Selection

Phase 0.28 introduces a progressive tag-style selector foundation for multi-value admin fields.

## Why

Native multi-select dropdowns are easy to misuse because editors can accidentally lose previous selections if they forget Ctrl/Cmd behaviour. A tag-style selector shows selected values clearly and lets editors remove individual values safely.

## Implementation direction

The foundation uses checkbox inputs as the canonical form state and progressively enhances them into removable tags with JavaScript. This keeps the form accessible and safe without JavaScript.

## Open-source options

Tom Select is documented as a dynamic, framework-agnostic and lightweight select UI control with autocomplete and native-feeling keyboard navigation, useful for tagging and contact lists. Choices.js is documented as a vanilla, lightweight, configurable select box/text input plugin without a jQuery dependency.

## PCI-aware note

Tag selectors should be used only for non-sensitive relationship fields such as page-to-site assignments. They must not be used for OTPs, TOTP secrets, recovery-code plaintext or payment data.
