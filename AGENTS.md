# Agent notes

## Plugin facts

- Product: Magic Page Query Helper
- Main file: `magic-page-query-helper.php`
- Slug: `magic-page-query-helper`
- Prefix: `mpqh` (classes `MPQH_*`, hooks and options `mpqh_*`)
- Text domain: `magic-page-query-helper`
- Floor: PHP 7.4+, WordPress 6.0+
- Current release: 1.0.1 (`Version`, `MPQH_VERSION`, and `readme.txt` Stable tag)
- Author: Duppins Technology (https://duppinstech.com)
- License: GPL-2.0-or-later
- Repo: https://github.com/duppinstech/magic-page-query-helper (public)
- Updater: `includes/class-mpqh-github-updater.php`. Sites install updates from GitHub Releases for `duppinstech/magic-page-query-helper`. Disable with `MPQH_DISABLE_GITHUB_UPDATER`.
- Pro add-on: none in this repository
- Integrations: GitHub Releases API, and the existing `location` taxonomy
- Security-sensitive: `includes/class-mpqh-github-updater.php` (update check and package URL) and the Settings → MPQH Dashboard callback in `magic-page-query-helper.php` (`manage_options`; it can show files under `wp-content`)

## What this is

WordPress plugin. PHP 7.4+, WordPress 6.0+. No Composer and no npm.

Magic Page Query Helper reduces database load on WordPress sites that use Magic Page, or a similar location-page generator, with a very large `location` taxonomy. It loads location term data in bulk and serves later lookups from that result. It does not modify Magic Page's files, and it does not replace a full-page cache.

There is nothing to configure. Settings → MPQH Dashboard is a status screen.

## Architecture

- `magic-page-query-helper.php` — boots the updater and the plugins-screen module, then `MPQH_Magic_Page_Query_Helper` bulk-loads `location` term meta, short-circuits `get_term_metadata`, primes the term cache, and registers Settings → MPQH Dashboard.
- `includes/class-mpqh-github-updater.php` — checks GitHub Releases and offers `magic-page-query-helper.zip` through the WordPress update transient.
- `includes/class-magic-page-query-helper-plugin-info.php` — plugins-screen Settings, Sponsor, View details, and Support links, and the View details modal read from `readme.txt`. No help URL is configured.
- `uninstall.php` — deletes the `mpqh_github_release` site option.
- `assets/wporg/` — plugins-screen banner and icon.
- `bin/build-zip.sh` — builds `magic-page-query-helper.zip` from tracked files.
- `bin/release-notes.sh` — reads the `## [x.y.z]` section from `CHANGELOG.md`.
- `index.php` — empty stub so directory listing stays quiet.

## Commands

Install: none. Copy this directory to `wp-content/plugins/magic-page-query-helper` and activate it in WordPress.

Lint (Git Bash, same command as CI):

```bash
find . -path ./build -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

Test: none. There is no PHPUnit project and no test command. Do not add one unless asked.

Release-notes check (Git Bash). `<version>` is the plugin header `Version`:

```bash
bash bin/release-notes.sh <version> magic-page-query-helper.php
```

Build the install zip (Git Bash). Output `magic-page-query-helper.zip` is gitignored:

```bash
bash bin/build-zip.sh
```

## Conventions

- PHP uses tabs. Text files use LF (`.gitattributes`, `.editorconfig`).
- Keep the plugin header `Version`, `MPQH_VERSION`, and `readme.txt` `Stable tag` identical.
- `CHANGELOG.md` uses Keep a Changelog. `bin/release-notes.sh` reads `## [x.y.z]` from that file and ignores `readme.txt` while `CHANGELOG.md` exists. The heading must not contain "Unreleased", and the section must not be empty. Keep the same notes in `readme.txt` under `== Changelog ==`.
- `readme.txt` is the WordPress plugin readme. `README.md` is the repository readme.
- Branches: `feature/…`, `fix/…`, `chore/…`. Pull requests target `main`.
- Commit subjects: `type: summary`, imperative. Types: `feat`, `fix`, `docs`, `ci`, `chore`.

## Hard rules

- Do not modify Magic Page's files. This plugin only preloads `location` term data.
- Do not commit `.env`, `.env.*`, `CLAUDE.local.md`, or `.claude/settings.local.json`.
- Do not change `LICENSE` or `SECURITY.md` unless asked.
- Do not retarget the GitHub repository used for updates unless asked.
- `assets/wporg/` is the plugins-screen banner and icon.
- New development-only paths must be added to the exclusion list in `bin/build-zip.sh`. That script builds the zip from tracked files.

## Agents

| Agent | Use |
| --- | --- |
| `product-reviewer` | Check a change against `INTENT.md` and `PLAN.md`. |
| `security-reviewer` | Updater, dashboard, and any new request input. |
| `ux-reviewer` | Dashboard and plugins-screen markup. |
| `support-docs-reviewer` | Changelog, `readme.txt`, `README.md`, and admin strings. |
| `legal-reviewer` | License, `assets/wporg/` images, and `OWNERSHIP.md`. |
| `marketing-reviewer` | Release-note highlights and listing copy. |
| `finance-reviewer` | A paid API, a new tier, or a price. |
| `release-ops-reviewer` | Version, zip contents, updater, and uninstall before a tag. |
| `test-writer` | PHPUnit tests after a behavior change. There is no suite yet. |
| `research` | WordPress core or GitHub API behavior. |
| `php-lint` | `.claude/hooks/php-lint.php` lints PHP on Edit and Write via `.claude/settings.json`. |
