# Environment bootstrap ownership

`bootstrap/autoload.php` is Zoosper's single environment bootstrap. It owns:

- fail-fast Composer autoloading;
- `.env` file parsing;
- normalisation of quoted values, inline comments and leading `export`;
- the guarded global `env()` lookup helper.

The unused `Core\Bootstrap\EnvLoader`, duplicate `Core\Env\EnvFileLoader`,
and tool-specific parser/helper path were retired. `tools/bootstrap.php` now
delegates to the application bootstrap and returns the repository root.

This prevents HTTP, CLI and repository tools from interpreting the same `.env`
file differently. The existing `EnvHelperTest` remains the behavioural contract
for falsy values, quotes, comments, exports, blanks and defaults.

The current helper is process-global by design. A future injectable configuration
or environment service should replace it only as a coordinated application
contract, not by introducing another parallel parser.
