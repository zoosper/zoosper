# Single named-view form

The Grid workspace previously rendered two independent view-name fields because `save_view` and `set_default_view` were separate forms. The workspace now renders one shared named-view form with two submit buttons. The clicked button supplies the mutation action, preserving the existing server contracts without JavaScript.

Available actions are:

- Save view
- Save & make default

Column preference actions remain separate. Existing active-view update, default and delete behaviour remains owned by the established view-action layer.
