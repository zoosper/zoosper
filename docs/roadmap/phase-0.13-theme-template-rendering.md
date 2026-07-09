# Phase 0.13 - Theme and Template Rendering

The current page renderer is intentionally simple. The next frontend platform phase should introduce proper template rendering.

## Goals

- move raw HTML strings out of `PageRenderer`
- introduce a theme resolver
- support template files under `themes/<theme>/templates`
- support page layout selection
- prepare for header/footer menu rendering
- keep default frontend minimal and fast

## Candidate template engines

- Marko-native rendering modules if they fit Zoosper's architecture
- plain PHP templates for the first minimal version
- Twig/Latte later if needed
