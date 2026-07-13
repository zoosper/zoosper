# Phase 1.14 - AdminUser Save Data Pipeline

This phase adds the AdminUser-specific save-data pipeline services that bridge submitted form data and the field-definition write map.

## Components

- `AdminUserSaveDataFactory` collects submitted form values into `EntityDataObject` and normalises locale values.
- `AdminUserCoreWriteDataMapper` maps the data object into SQL-safe admin_users core-column data using `AdminUserFieldRegistryFactory`.
- `AdminUserSavePipelineContextFactory` creates `EntitySaveContext` objects for future validation/save events.

## Key rule

The submitted values stay available for handlers and modules, but only field-definition `CoreColumn` values become core write data.
