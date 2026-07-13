# Phase 1.12 - Admin Entity Save Pipeline Foundation

This phase introduces the foundation for modular admin save flows.

## Problem

Directly patching individual controllers/repositories for every extra field does not scale. Third-party modules need a safe way to add form fields, validate them, mutate them, and persist their own values without touching core code.

## Foundation

The new foundation provides:

- `EntityDataObject` with `setData()` / `getData()`.
- `FieldDefinition` for declaring field purpose and storage.
- `FieldDefinitionRegistry` for entity/form write maps.
- `FieldStorageType` for core columns, extension tables, handlers and virtual fields.
- `EntitySaveContext` for validation and lifecycle events.
- `EntitySaveLifecycle` standard event names.

## Rule

All submitted values may enter the data object. Only declared `CoreColumn` fields may enter the core SQL write map.
