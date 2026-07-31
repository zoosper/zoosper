# Package extraction direction

Zoosper will progressively split large application modules into smaller focused
Composer packages under `packages/`, following the same maintainable and
pluggable style used by Marko packages.

Extraction rules:

- extract at a clear capability and dependency boundary;
- define contracts before moving implementations;
- avoid circular package dependencies;
- keep API and headless behaviour independent from optional presentation packages;
- preserve runtime behaviour and migrations during each move;
- expose source packages to runtime only through Composer-installed `vendor/`;
- delete superseded code after parity is proven;
- keep tests co-located but excluded from production exports.

Candidate capability boundaries should be audited before sequencing. The work
must not become a mechanical folder split of tightly coupled code.
