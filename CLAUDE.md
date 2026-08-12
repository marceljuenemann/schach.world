# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**schach.world** is a web application suite for the German chess federation (NSV) managing:
- **League Manager** – team self-service result entry, ranking, statistics, PDF/SWI exports for 25,000+ matches since 2006
- **Tournament Registration** – customizable registration with DWZ (German chess rating) integration, waiting lists, CSV/Swiss Chess export
- **Tournament Results** – Swiss Chess file uploads with multi-division group support

## Commands

### PHP / Symfony
PHP commands must be run **inside the Docker container** (ensures correct PHP version and DB connection strings):
```bash
docker exec nsv-webserver ./bin/phpunit                                              # run all tests
docker exec nsv-webserver ./bin/phpunit --update-snapshots                           # regenerate snapshot fixtures
docker exec nsv-webserver ./bin/phpunit tests/League/Controller/LeagueControllerTest # run a single test file
docker exec nsv-webserver ./bin/console cache:clear
docker exec nsv-webserver ./bin/console doctrine:migrations:migrate --em main
```

### Angular (`ng/`)
```bash
npm install
ng serve          # dev server
ng test           # Karma + Jasmine unit tests
npm run build     # production build → public/core/ng-build/ (CI does this automatically)
```

### React (`javascript/`) — legacy, being phased out
```bash
npm install
npm start         # dev server
npm test
npm run build     # production build → public/core/js-build/
```

### Docker dev environment (`dev/`)
```bash
docker compose up -d                            # start containers
docker exec -it nsv-webserver /bin/bash         # shell into PHP container
dev/scripts/run-tests.sh                        # run PHP tests inside container
```

## Architecture

### Request routing (the tricky part)
All traffic enters through `public/.htaccess`. Static files are served directly; everything else hits WordPress (`public/wordpress/`). The `nsv-v3` WordPress plugin maintains an allowlist of URL prefixes that are forwarded to the **Symfony** application instead. Legacy routes fall through to the old PHP code in `public/ligen/` via Symfony's `LegacyController`.

```
Browser → .htaccess → WordPress (nsv-2020 theme)
                     ↘ nsv-v3 plugin allowlist → Symfony app
                                                 ↘ LegacyController → public/ligen/
```

### PHP / Symfony (`src/`)
Symfony 6.4 with Doctrine ORM. Autowiring is on; services are in `config/services.yaml`.

| Module | Responsibility |
|---|---|
| `Nsv\WebApp` | **Core of the Symfony app** – calendar, club pages, WordPress rendering bridge, email templates |
| `Nsv\League` | League manager domain (largest module) – entities (League, Division, Team, Player, Pairing, Game, Round, Date), ranking/schedule/statistics services, REST API models, tie-break algorithms (Berlin, HeadToHead, Scores) |
| `Nsv\Registration` | Tournament registration entity + controller |
| `Nsv\Dwz` | Proxy to the external DWZ (German chess rating) API for player lookups |
| `Nsv\Util` | Twig helper functions, RSS/article feed fetching, text sanitization |

### Frontend
- **Twig** – used for simple routes with minimal interactivity
- **Angular** (`ng/`) – used for highly interactive views; the direction for future interactive UI
- **React** (`javascript/`) – maintained but not growing; handles club map and some team/division views

All UI-facing text must be in **German**. Code (variables, functions, comments) is written in **English**.

### Testing approach
PHP tests use **snapshot assertions** (Spatie) extensively — HTML/JSON responses are compared to fixtures stored in `tests/**/__snapshots__/`. Tests run in transactions that roll back (Dama DoctrineTestBundle) **for the `main` entity manager only**. When a test output legitimately changes, run with `UPDATE_SNAPSHOTS=1` to regenerate.

**⚠️ The `league` entity manager is NOT rolled back.** Its tables (`spieler`, `mannschaften`, `staffeln`, `turniere`, etc.) are **MyISAM**, which has no transaction support at all. Any test that persists/flushes `Nsv\League\Entity\*` entities (Team, Player, Division, ...) writes **permanently** to the real, shared dev database. Consequences:
- Any test creating League entities **must delete everything it created** (players, teams, etc.) at the end of the test.
- Never call destructive League operations (delete, bulk renumber) against a real/shared fixture entity (e.g. `$this->team` in `PlayerServiceTest`) — only against entities the test itself created and will clean up.
- If you notice unexplained extra rows or altered data in League tables, suspect a prior test run rather than fixture corruption.

### Database
MySQL 5.7. Symfony manages two databases: `nsv-main` (league/app data) and optionally a DWZ mirror. WordPress uses its own separate database outside of Symfony. Doctrine migrations live in `/migrations/` and target entity manager `main`. The League schema's tables are MyISAM (legacy, no transactions) — see the testing caveat above.

### Registration system

**Tournament config files** live outside `src/` at `/data/registration/{tournament}.php`. Each returns a `TournamentConfig` instance defining groups, constraints, additional fields, managers, deadline, and email settings. The tournament ID comes from the URL and is used to load the config dynamically.

**DWZ null-coalescing pattern**: `PlayerRegistration` stores `null` for any field that matches the player's current DWZ data. At read time, `fromEntity()` falls back to the DWZ database value. This means: only store a value when it *differs* from DWZ. When writing code that reads registration data, always go through `fromEntity()` — never read entity fields directly for display.

**Waiting list confirmation** is not a separate endpoint — it's a regular `PUT` update that sets `waitlist: false`. The controller detects the `waitlist→active` transition and sends a different confirmation email.

**Manager access** is determined by WordPress auth (`Auth::isAdmin()` or matching `TournamentConfig->managers`). Managers see sensitive fields (contact details, year of birth, additional fields) and can bypass deadline and validation constraints.

**Cross-group constraints** (`RegistrationConstraint`) cap combined registrations across multiple groups. Available slots for a group = minimum of (group limit, tournament limit, all applicable constraint limits). This logic lives in `Tournament` / `Group` Angular models, not on the server — the server only enforces group-level capacity.

## Git workflow
- **Small changes**: Make the changes and leave them uncommitted. Let the user handle the commit.
- **Larger changes**: Ensure we are on a feature branch (not `main`) before starting. If we're on `main`, ask the user to create a feature branch first. Commit and push automatically, then open a pull request and ask the user to review it. If the changes involve UI or anything that needs local verification, say so explicitly and ask the user to test before reviewing the PR.
- **If unsure** whether a change qualifies as small or large, ask before committing.

## Key conventions
- PHP 8.4+ — use modern PHP features (readonly properties, named args, enums where appropriate)
- Never edit existing Doctrine migrations — always create a new one. Always pass `--em main`.
- Snapshot tests (`tests/**/__snapshots__/`): a failing snapshot means either a real bug or an intentional output change. Only run `--update-snapshots` when the change is intentional.
- New interactive UI goes in Angular from the start — don't add client-side logic to Twig templates as a stepping stone.
- Before modifying `public/ligen/`, confirm with the user what is safe to delete — it's not always obvious what's still in use vs. dead code.
- Before adding a new `Nsv\*` module, check whether the feature fits an existing one; the module boundaries aren't always obvious.
- The DWZ integration calls an external API; use `Nsv\Dwz\DwzController` / `SchachInClient` for any player rating lookups.
- Angular build artifacts are committed to `public/core/ng-build/` — CI generates them automatically, so there's no need to run `ng build` manually. If you do run it locally (e.g. to verify a change), it's fine to commit the regenerated output rather than reverting it.
