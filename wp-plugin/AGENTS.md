# AGENTS.md

Guidance for AI coding agents working on this repository.

## Project

**schach-world-wp** is the WordPress plugin "schach.world": tournament
management for chess clubs (registration with DWZ lookup, later Swiss Chess
result upload and pairings). See [README.md](README.md) for scope and roadmap.

Architecture decisions are recorded in [docs/decisions/](docs/decisions/), and
unresolved topics in [docs/open-questions.md](docs/open-questions.md). Read the
relevant decisions before making structural changes. When a decision changes,
update or add a decision record rather than silently diverging from it.

> This directory currently lives inside the `nsv` repository (`wp-plugin/`) and
> will become its own repository. **Never depend on anything outside this
> directory** (no imports, paths, scripts or config from the NSV / Symfony app).
> Code ported from there is copied, not referenced.

## Layout

| Path | Contents |
|---|---|
| `plugin/` | The WordPress plugin exactly as shipped. Main file `schach-world.php`, `readme.txt` (WordPress.org format), PHP classes in `src/` (namespace `SchachWorld\`, PSR-4). |
| `docs/` | Decision records and open questions. |
| `.wp-env.json` | Local WordPress environment (`@wordpress/env`). |

Planned (not yet present): `frontend/` (Angular app, built into
`plugin/build/`), `e2e/` (Playwright).

## Commands

Requires Node.js and Docker.

```bash
npm install
npm run env:start                    # WordPress at http://localhost:8888 (admin / password)
npm run env:stop
npm run env:reset                    # reset the database
npm run wp -- plugin list            # any WP-CLI command
curl http://localhost:8888/wp-json/schach-world/v1/status   # smoke test
```

`plugin/` is mounted into the container as `wp-content/plugins/schach-world`,
so PHP changes are live without a restart. `wp-env start` activates the plugin
and enables pretty permalinks.

## Conventions

- **Prefix:** everything uses the plugin prefix in the form its context allows
  ([0005](docs/decisions/0005-naming-prefix.md)):
  - `SchachWorld\` namespace
  - `schach_world_` hooks, options and tables
  - `schach-world/v1` REST namespace
  - `schach-world-` custom elements
  - `--schach-world-` CSS variables
- **PHP 8.2:** the code must run on PHP 8.2, the minimum we support. Don't use
  newer features (typed class constants, property hooks, asymmetric
  visibility, etc.). wp-env runs PHP 8.2 so incompatibilities show up locally.
- **No runtime Composer dependencies:** bundled libraries would need namespace
  prefixing (Strauss) to avoid clashing with other plugins. Prefer WordPress
  APIs; ask before adding a dependency.
- **WordPress security basics:**
  - Every REST route has a real `permission_callback`.
  - Sanitize input, escape output, and use `$wpdb->prepare()`.
  - Every PHP file starts with `defined('ABSPATH') || exit;`.
- **Language:** user-facing text is German. Code, comments and docs are
  English.
- **License:** AGPL-3.0-or-later. Bundled third-party code must be
  GPL-compatible.

## Git workflow

- **Small changes:** make them and leave them uncommitted.
- **Larger changes:** work on a feature branch, commit, push and open a pull
  request. If the change needs manual verification (UI, WordPress behavior),
  say so.
- **If unsure** whether a change is small or large, ask.
