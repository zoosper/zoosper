# Entity extension data persistence documentation seed

Module authors can use `FieldDefinition::extension()` to declare module-owned fields. Those fields remain available in `EntityDataObject` and can be persisted through `EntityExtensionDataPersister` without adding columns to core tables.
