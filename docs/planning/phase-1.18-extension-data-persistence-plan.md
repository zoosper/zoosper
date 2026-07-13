# Phase 1.18 Planning - Entity Extension Data Persistence for Third-party Fields

## Purpose

Zoosper now has a working AdminUser locale save path and the generic AdminUser save pipeline foundations are in place. The next architectural step is to support third-party module fields without touching core tables or controllers.

This planning phase documents the recommended direction before implementation.

## Current completed work

- Admin user locale field is rendered in the admin user form.
- Admin user locale persists successfully through the concrete create/update save path.
- PDO SQL placeholder and execute-parameter parity is now verified.
- Admin success and error notices are visibly styled.
- Admin entity save pipeline foundation exists.
- AdminUser field definitions and safe write maps exist.
- AdminUser save data factory, core write mapper, save pipeline context and SQL builder exist.

## Problem to solve next

Third-party modules need to add fields to admin forms and have those fields available during save without modifying core code.

Examples:

- A security module adds a custom admin-user approval flag.
- An HR module adds an internal staff reference field.
- A partner module adds module-specific admin notes.
- A localisation module adds extra locale metadata.

These fields must not be blindly persisted into `admin_users`, because unrelated columns would break SQL and weaken modularity.

## Recommended design

Use the existing `EntityDataObject` and `FieldDefinitionRegistry` foundation, then add extension-data storage.

```text
Form POST
  -> EntityDataObject
  -> FieldDefinitionRegistry
  -> CoreColumn write map saves known core columns
  -> ExtensionTable fields save to entity_extension_values
  -> Handler fields are processed by dedicated services
  -> Virtual fields are ignored for persistence
```

## Proposed generic table

```sql
CREATE TABLE entity_extension_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(120) NOT NULL,
    field_name VARCHAR(120) NOT NULL,
    value_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_entity_extension_field (entity_type, entity_id, module, field_name),
    KEY idx_entity_extension_lookup (entity_type, entity_id),
    KEY idx_entity_extension_module (module)
);
```

## Plain-English explanation

Think of the core table as the official form register, where only approved fields are allowed. Third-party fields are still collected, but they are kept in a separate extension-value table unless the field is explicitly declared as a core column.

## Pros

- Keeps core tables stable and safe.
- Allows third-party modules to add data without touching core schema.
- Prevents SQL errors from unknown form fields.
- Works across many entity types, not just admin users.
- Supports future marketplace/community modules.
- Fits Magento-style extension attributes and Drupal-style field-definition thinking.

## Cons

- More moving parts than direct SQL columns.
- Extension values stored as JSON need validation and normalisation.
- Querying extension values is slower than querying real indexed columns.
- Some high-performance module fields may still need module-owned tables.
- Developers need clear documentation so they know when to use core column, extension table, handler or virtual storage.

## Recommended decision

Implement a generic `EntityExtensionValueRepository` and `EntityExtensionDataPersister` first. Keep it small and explicit. Do not migrate all core fields to extension data. Core fields should stay in core tables.

## Acceptance criteria for implementation phase

- Migration creates `entity_extension_values`.
- Repository can upsert extension values by entity type, entity id, module and field name.
- Persister reads `FieldDefinitionRegistry::extensionData()` and saves only extension fields.
- Verifier proves rogue fields are not saved.
- Verifier proves extension fields are saved separately.
- Documentation explains when developers should use each field storage type.
