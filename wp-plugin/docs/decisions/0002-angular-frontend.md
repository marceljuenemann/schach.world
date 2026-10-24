# 0002: Angular frontend, logic in TypeScript, no shared packages for now

**Status:** Accepted (2026-10-24)

## Context

The plugin's UI is embedded into arbitrary WordPress sites, possibly next to
other plugins that also ship JavaScript frameworks. On the NSV site it may also
run on the same page as the league manager's Angular bundle.

The plugin and the league manager have similar UI pieces (player dialog, tables,
DWZ search). We considered a shared Angular library published to npm, an
OpenAPI contract and a generated TypeScript client. That only pays off once
there is a second consumer, and right now there isn't one we can name.

## Decision

- **Frontend:** Angular, delivered as custom elements via `@angular/elements`,
  as today. The plugin's frontend and admin UI live in one Angular app.
- **Zoneless:** use `provideZonelessChangeDetection()` and drop `zone.js`, so we
  don't conflict with other Angular or zone.js code on the same page.
- **Prefixed element names:** all custom element tag names are prefixed
  (`schach-world-`, see [0005](0005-naming-prefix.md)), because
  `customElements.define` throws on duplicates.
- **Logic in TypeScript:** domain logic lives in TypeScript wherever possible:
  registration constraints (already client-side), tie-breaks and the future
  pairing engine. The server still enforces anything that guards against races
  or abuse, such as group capacity.
- **No shared packages (YAGNI):** all TypeScript code lives in the plugin's
  frontend app.
  - No npm packages and no shared library with the league manager.
  - No OpenAPI spec or generated client: the REST API is called from a
    hand-written, typed Angular service.

## Consequences

- **Code may be duplicated:** if the league manager needs a component, it is
  copied for now. A package is extracted once a real second consumer exists.
  - Keep that extraction cheap: components don't depend on WordPress globals,
    and API access goes through one service.
- **Same page:** when the plugin and the league manager both load on one page,
  two Angular runtimes run side by side. This works because:
  - Both apps are zoneless. The league manager has to drop `zone.js` too.
  - The apps use different tag prefixes (`nsv-` for the league manager,
    `schach-world-` for the plugin).
