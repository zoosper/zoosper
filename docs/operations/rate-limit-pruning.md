# Rate-limit bucket pruning

Zoosper exposes `rate-limit:prune` as the canonical maintenance operation for expired database-backed fixed-window buckets. Scheduling belongs to the deployment platform. Zoosper does not start a scheduler, daemon, queue worker, or web endpoint for this operation.

## Prerequisites

- Apply database migrations before enabling rate-limit enforcement or scheduling pruning.
- Run the command as the same least-privileged deployment user that is permitted to read the application environment and connect to the database.
- Use the deployed PHP 8.5 CLI binary and an absolute application path.
- Keep the command inaccessible from HTTP routes.

Run the command manually before scheduling it:

```bash
cd /srv/zoosper && /usr/bin/php8.5 bin/zoosper rate-limit:prune
```

A successful run prints the deleted-row count and exits with status `0`. Invalid arguments and runtime failures return a nonzero status. Repeating a successful run is safe: after eligible rows are removed, the next run reports zero unless more buckets have expired.

## Cron example

The following example runs every 15 minutes, prevents overlapping invocations with `flock`, and appends both output streams to an operations log:

```cron
*/15 * * * * flock -n /tmp/zoosper-rate-limit-prune.lock sh -c 'cd /srv/zoosper && /usr/bin/php8.5 bin/zoosper rate-limit:prune' >> /var/log/zoosper/rate-limit-prune.log 2>&1
```

Replace the application, PHP, lock, and log paths for the deployment. Monitor nonzero exit statuses and ensure the log is rotated by the hosting platform.

## systemd example

Create a deployment-owned oneshot service:

```ini
[Unit]
Description=Prune expired Zoosper rate-limit buckets
After=network.target

[Service]
Type=oneshot
User=www-data
WorkingDirectory=/srv/zoosper
ExecStart=/usr/bin/php8.5 /srv/zoosper/bin/zoosper rate-limit:prune
```

Create the matching timer:

```ini
[Unit]
Description=Run Zoosper rate-limit pruning every 15 minutes

[Timer]
OnBootSec=5m
OnUnitActiveSec=15m
Persistent=true

[Install]
WantedBy=timers.target
```

Replace the service user and paths for the deployment. The service and timer are examples only; Zoosper does not install or enable host scheduler configuration.

## Concurrency and deletion semantics

Pruning executes one indexed database statement using `window_ends_at <= now`. Active windows are outside the deletion predicate. If deployments permit overlapping runs, the affected-row count may be divided between them; the database predicate remains the source of truth. Use a host-level overlap guard such as `flock` where available to keep logs and operational behavior simple.

The current command intentionally has no batch-size option. Add bounded deletion only if measured production evidence shows that the indexed deletion causes unacceptable database pressure.
