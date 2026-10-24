# 0004: DWZ data is imported locally by the plugin

**Status:** Accepted (2026-10-24)

## Context

Registration looks up players in the DWZ (German rating) database. Today,
`Nsv\Dwz\Command\DwzUpdateCommand` downloads the DSB archive (`spieler.csv`,
`vereine.csv`) and imports it into a local table, which the Symfony app queries.

## Decision

- **Local import:** the plugin imports the same DSB archive into its own custom
  tables. For now, the code is duplicated from `Nsv\Dwz` rather than shared.
- **Chunked background import:** the import runs as chunked background jobs via
  Action Scheduler, because shared hosting typically limits execution time to
  about 30 seconds and WP-Cron only runs on page visits.
- **No central service:** schach.world is not a mandatory proxy for DWZ data. A
  hosted lookup may be offered later as an optional fallback.
- **Rating source abstraction:** player lookup goes through a rating-source
  interface. DWZ is the only implementation initially; FIDE or ÖSB Elo could
  follow.

## Consequences

- **Disclosure:** the download from schachbund.de must be disclosed as an
  external service in `readme.txt`.
- **DSB terms:** the DSB's terms for using the export need to be checked before
  publishing.
- **No infrastructure dependency on us:** each site holds its own copy of the
  data. We don't operate infrastructure that every installation depends on, and
  we don't process their lookups.
