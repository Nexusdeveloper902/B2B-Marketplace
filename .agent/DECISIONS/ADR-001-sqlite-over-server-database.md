# ADR-001: SQLite as the persistence layer for the storefront

## Date
2026-09-02

## Context
The marketplace storefront is a low-stakes, single-table marketing application. The
only persisted data is contact/demo requests submitted through the contact form.
The task constraints mandate SQLite and forbid configuring MySQL/Postgres. This ADR
records that decision so a future agent does not "upgrade" the app to a server
database out of habit.

## Decision
Use Laravel's SQLite driver with a single `database/database.sqlite` file.
`DB_CONNECTION=sqlite` in `.env`. The database file itself is git-ignored;
`php artisan migrate` creates it automatically (verified on Laravel 13).

## Alternatives Considered
- MySQL/Postgres — rejected: requires an external service for a one-table app,
  violates task constraints, complicates fresh-clone demoing.
- No database at all (write submissions to a log file) — rejected: the task
  explicitly requires a `contact_requests` table with an Eloquent model and migration.

## Reasoning
Zero external services, zero configuration, instant fresh-clone setup. The write
load is a handful of form submissions per day at most.

## Consequences
- If this app ever needed concurrent write-heavy usage or multi-server deployment,
  it would need a database migration path. That is not expected for a storefront.
- The default Laravel 13 users/cache/jobs migrations were removed so the database
  footprint is exactly the `contact_requests` table; sessions and cache run on the
  file driver (see `.env`), so no extra tables are needed.

## Status
ACTIVE

## Supersedes
None
