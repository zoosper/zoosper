# Modular Entity Save Pipeline Summary

## Correct terminology

| Informal wording | Recommended term | Meaning |
|---|---|---|
| DB collection object | Entity data object / data transfer object | Carries submitted values; does not run SQL. |
| setData object | Data object / data bag | Object with `setData()` and `getData()`. |
| Actual query preparation | Repository / query builder / write mapper | Converts approved data into SQL-safe writes. |
| Sub-module data processing | Event observer / plugin / handler | Lets modules validate, mutate or persist data. |
| Extra fields | Extension attributes / extension data | Module-owned data attached to an entity. |

## Storage types

- `CoreColumn`: field is saved into the entity's main table.
- `ExtensionTable`: field is saved into extension-value storage.
- `Handler`: field is processed by a specialised service, such as password or role assignment.
- `Virtual`: field exists only for request/form logic, such as CSRF tokens.

## Design rule

All submitted values may enter `EntityDataObject`, but only declared `CoreColumn` fields may enter core SQL.

## Why this matters

This prevents problems such as:

- Unknown POST values breaking SQL.
- Third-party modules editing core controllers.
- Security-sensitive fields being saved through the wrong path.
- SQL placeholders not matching execute parameters.

## Current AdminUser status

AdminUser locale persistence is now working through repository create/update paths. The current implementation is a practical bridge. Future phases should migrate broader save behaviour toward a complete pipeline with extension-data persistence and lifecycle events.
