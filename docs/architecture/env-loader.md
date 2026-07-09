# EnvLoader

`Zoosper\Core\Bootstrap\EnvLoader` is now a real class so IDEs can resolve the import used by CLI scripts.

It reads `.env` lines in `KEY=value` format and populates `$_ENV` and `getenv()` values when they have not already been set by the environment.
