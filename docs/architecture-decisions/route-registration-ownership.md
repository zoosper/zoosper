# Route registration ownership and duplicate rejection

## Status

Accepted for the current development line.

## Context

Zoosper owns the live HTTP dispatcher. Module route arrays are registration inputs to that one dispatcher. Marko routing is not a second runtime route stack and is not adopted by implication from other Marko packages.

At the captured `2da0359a` boundary, exact static duplicates replaced the earlier handler, while identical parameterised declarations remained registration-order dependent. The current first-party inventory contains no duplicate method-and-path keys, so rejecting duplicates does not require a compatibility exception.

The same inventory also shows authenticated-only Admin routes and controller-authorised PAT API routes. This decision therefore does not redefine route access metadata, move API authorisation into middleware, or change decoded parameter behaviour.

## Decision

- `Router` is the single live dispatcher.
- Registering the same normalised HTTP method and path more than once throws during route registration.
- The same path may be registered for different HTTP methods.
- Exact static routes continue to take precedence over parameterised matches.
- Existing HEAD, OPTIONS, 404, 405, stateless and fallback semantics remain unchanged.
- Broader route metadata, API authorisation and Marko attribute compatibility require separate evidence-backed decisions.

## Consequences

Duplicate ownership fails before request dispatch instead of silently replacing or shadowing a handler. Module authors must remove the duplicate or deliberately choose a distinct method or path. No override mechanism is introduced by this decision.
