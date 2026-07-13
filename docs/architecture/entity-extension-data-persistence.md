# Phase 1.19 - Entity Extension Data Persistence

This phase implements the first generic persistence layer for third-party module fields.

## Components

- `EntityExtensionValue` value object.
- `EntityExtensionValueRepository` for storing/retrieving extension values.
- `EntityExtensionDataPersister` for persisting `FieldStorageType::ExtensionTable` fields from `FieldDefinitionRegistry`.
- `database/schema/entity_extension_values.sql` schema seed.

## Design rule

Core columns stay in core entity tables. Third-party fields declared as `ExtensionTable` are persisted separately by entity type, entity id, module and field name.

## Not included yet

This phase does not yet wire extension data persistence into `UserAdminController`. That should happen after lifecycle events are introduced or during a focused AdminUser extension-data integration phase.
