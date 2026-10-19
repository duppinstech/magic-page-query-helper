# Architectural decisions

Append-only log. Format: `## YYYY-MM-DD — Title`, then **Context** / **Decision** / **Consequences**. Owner waivers of High findings are recorded here too.

## 2026-04-24 — Preload location term data

**Context:** Sites with a very large `location` taxonomy issue one query per term lookup. Magic Page, or a similar generator, registers that taxonomy. Initial commit `1187b27`.

**Decision:** Bulk-load location term meta and location terms, short-circuit `get_term_metadata`, and prime the term cache. Do not modify Magic Page's files.

**Consequences:** The `location` taxonomy must already exist. Settings → MPQH Dashboard is a status screen. There is nothing to configure.

## 2026-10-18 — Ship updates from GitHub Releases

**Context:** The plugin is installed from a zip, outside WordPress.org. Commit `af89884`.

**Decision:** `MPQH_GitHub_Updater` checks GitHub Releases for `duppinstech/magic-page-query-helper` and installs `magic-page-query-helper.zip`. `MPQH_DISABLE_GITHUB_UPDATER` turns that off.

**Consequences:** A `v*` tag on `main` is an update for sites running the updater. Uninstall deletes the `mpqh_github_release` site option and leaves `mpqh_last_request`.

## 2026-10-18 — Present the plugin on the plugins screen

**Context:** There is no WordPress.org listing page. Commit `6b78a0b`, released in 1.0.1.

**Decision:** `MPQH_Plugin_Info` builds the View details modal from `readme.txt` and `assets/wporg/`, and adds Settings, Sponsor, View details, and Support. No help URL is set.

**Consequences:** Listing copy lives in `readme.txt`. The banner and icon live in `assets/wporg/`.
