# Phase 0.59 progress report

## Feature name

Template engine adapter foundation.

## Implemented

- Added `TemplateEngineInterface`.
- Added `PhpTemplateEngine` as the compatibility renderer for existing `.php` templates.
- Added `TemplateEngineRegistry` to map file extensions to engines.
- Updated `TemplateRenderer` to render through the engine registry.
- Updated theme service registration to provide the engine registry.
- Added `config/template.php`.
- Added diagnostics, verification and licence notes.

## What remains

- Add Latte as the recommended default modern engine.
- Add a sample `.latte` frontend template.
- Convert default frontend templates to Latte after the adapter verifies cleanly.
- Add optional Twig adapter later if community demand exists.

## Risks or considerations

- Current templates remain PHP to avoid breaking admin/frontend immediately.
- Future template engines must preserve escaping and security rules.
- Licence compatibility must be documented before public stable release.
