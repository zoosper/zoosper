# Schema CLI error handling

`bin/zoosper-schema` uses the shared bootstrap autoloader, application config
loader, logging manager and exception handler before it creates PDO or executes
schema operations.

Connection and schema failures therefore travel through Zoosper's exception
logging and sensitive-value redaction path instead of being left to PHP's raw
uncaught-exception output.

The schema CLI remains database-oriented by design. This change provides a
consistent failure boundary; it does not make schema commands database-free.
