# Navigation Testing Strategy (Craft + Pest)

## Goal

Build an integration-first suite that boots real Craft and exercises Navigation behavior across real navs, nodes, queries, rendering, GraphQL, multisite, active-state handling, breadcrumbs, and performance baselines.

## Core Principles

- Tests run against a real Craft application instance.
- The suite uses an isolated test database, not the active local Craft site database.
- `ENVIRONMENT=testing` is required before the Craft bootstrap will run.
- Navigation is installed/enabled in test bootstrap before integration tests execute.
- Performance tests start as smoke profiles, then graduate into explicit budgets after baseline data is collected.

## Isolated Test Install

1. Run `composer install` from the plugin root if dependencies are not installed.
2. Run `composer test:setup` from the plugin root.
3. Edit `.env.testing` if your test database credentials differ from `.env.testing.example`.
4. Run `composer test`.
5. Run `composer test:perf` for performance smoke profiles.

`test:setup` will:

- create `.env.testing` from `.env.testing.example` if needed;
- configure Craft DB credentials in non-interactive mode using `CRAFT_DB_*` values;
- drop existing tables in the configured test database;
- install Craft fresh with test defaults.

The target database itself must already exist and be reachable by the configured DB user.

## Current Structure

```text
tests/
  Pest.php
  bootstrap.php
  bootstrap-craft.php
  General/
  Performance/
  Support/
  _craft/
  bin/
```

## First Coverage Targets

- Node element creation and query behavior.
- Tree rendering query counts and duplicate query patterns.
- Active-state URL matching.
- Breadcrumb resolution from request, URL, and element contexts.
- Multisite propagation and localized custom URL behavior.
- Import/export and lifecycle behavior around linked elements.
