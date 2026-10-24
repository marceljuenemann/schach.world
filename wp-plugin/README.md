# schach.world WordPress plugin

A reusable WordPress plugin for chess tournament management, extracted from the
schach.world / NSV system. It is intended for distribution via the WordPress.org
plugin directory and for hosted use on schach.world.

> This directory lives inside the `nsv` repository for now and is planned to
> become its own repository.

## Status

Planning. No code yet.

## Scope

1. **Tournament registration**: port of `Nsv\Registration` and the Angular
   registration UI, including DWZ lookup, waiting lists and exports.
2. **Result upload via Swiss Chess files**: port of the `nsv-turniere` plugin.
3. **Later:** an own pairing engine, growing into full tournament management.

The league manager is out of scope and stays in the Symfony application.

## Roadmap

### Milestone 1: Registration running on the NSV site

- [ ] **Repository structure and minimal plugin**
  - `frontend/`, `plugin/`, `e2e/`, root `package.json`, `.wp-env.json`
  - Plugin main file with header and `readme.txt`; plugin activates in `wp-env`
- [ ] **Tournament pages and routing** (see
  [0006](docs/decisions/0006-tournament-management-in-frontend.md))
  - Base page created on activation; rewrite rules for `turniere/[slug]/[year]/`
  - `schach_world_admin` capability; wp-admin redirect for non-admins
- [ ] **Frontend working**
  - Angular app (zoneless, Shadow DOM root) loaded by the plugin
  - Loaded from `ng serve` in development and from `plugin/build/` in production
- [ ] **First database table and REST API**
  - Custom table via `dbDelta` with schema versioning
  - First `schach-world/v1` endpoints with capability checks
- [ ] **DWZ import:** DSB archive into local tables, chunked via Action Scheduler
- [ ] **First frontend components:** base stylesheet, `--schach-world-*` tokens,
  dialog, form controls, table
- [ ] **Migrate the registration system**
  - Registration form, waiting list, exports
  - Tournament configuration in the database instead of `/data/registration/*.php`
- [ ] **Test on the NSV site**
  - Remove `/anmeldung/` from the `nsv-v3` allowlist so WordPress handles it
  - Redirect old `/anmeldung/{tournament}/` URLs to the new tournament pages

### Later

- [ ] Result upload via Swiss Chess files (port of `nsv-turniere`)
- [ ] Submission to the WordPress.org plugin directory
- [ ] Own pairing engine

## Decisions

| # | Decision |
|---|---|
| [0001](docs/decisions/0001-wordpress-plugin-with-thin-php-backend.md) | WordPress plugin with a thin PHP backend |
| [0002](docs/decisions/0002-angular-frontend.md) | Angular frontend, logic in TypeScript, no shared packages for now |
| [0003](docs/decisions/0003-styling-shadow-dom-and-css-variables.md) | Styling: Shadow DOM, no Bootstrap, CSS variables as the only styling API |
| [0004](docs/decisions/0004-dwz-data-import.md) | DWZ data is imported locally by the plugin |
| [0005](docs/decisions/0005-naming-prefix.md) | Naming prefix `schach-world` |
| [0006](docs/decisions/0006-tournament-management-in-frontend.md) | Tournament management in the frontend, not wp-admin |

Unresolved topics are tracked in [open questions](docs/open-questions.md).
