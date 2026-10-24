# 0005: Naming prefix `schach-world`

**Status:** Accepted (2026-10-24)

## Context

WordPress plugins share one global namespace per site: PHP functions, hooks,
options, database tables, custom element names and CSS variables can all collide
with other plugins. WordPress.org reviewers expect a distinctive prefix.

## Decision

Everything is prefixed with the plugin name, in the form each context allows:

| Where | Form |
|---|---|
| CSS variables, `::part()` names | `--schach-world-accent` |
| Custom elements | `schach-world-registration` |
| PHP namespace | `SchachWorld\` |
| Hooks, options, global functions | `schach_world_` |
| Database tables | `{$wpdb->prefix}schach_world_*` |
| REST namespace | `schach-world/v1` |
| Plugin slug / text domain | `schach-world` |

Internal CSS tokens use a short private form (`--_accent`), since they are not
part of the public API.

## Consequences

- **Readable, unlikely to collide:** the public names are long but
  self-explanatory in a site owner's theme CSS.
- **Tied to the brand:** if the plugin ends up with a different name on
  WordPress.org, the prefix changes with it. This must be settled before the
  first public release, because renaming afterwards breaks sites' CSS
  customizations and the slug can't be changed after approval.
