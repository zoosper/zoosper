# Phase 1.20 - Entity Save Lifecycle Events

This phase introduces the event infrastructure needed for modular save flows.

## Components

- `EntitySaveEventListenerInterface`
- `EntitySaveEventDispatcherInterface`
- `EntitySaveEventDispatcher`
- `EntitySaveLifecycleRunner`

## Lifecycle stages

The runner coordinates these lifecycle constants from `EntitySaveLifecycle`:

```text
entity_save.data_collect.before
entity_save.data_collect.after
entity_save.validate.before
entity_save.validate.after
entity_save.save.before
entity_save.save.after
entity_save.commit.after
```

## Design rule

Controllers and repositories should not know about every third-party module. Modules should listen to lifecycle events to validate, mutate, persist or react to entity save data.

## Plain-English explanation

The save pipeline is like a checkpoint system. Before data is saved, modules get a chance to validate or adjust it. If validation errors exist, the save is stopped. After the save succeeds, modules can react without modifying the controller.
