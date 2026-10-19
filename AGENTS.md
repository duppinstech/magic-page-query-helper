# Agent notes

WordPress plugin. PHP 7.4+, WordPress 6.0+. No Composer and no npm.

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

## Do not touch

- Do not modify Magic Page's files. This plugin only preloads `location` term data.
- Do not commit `.env`, `.env.*`, `CLAUDE.local.md`, or `.claude/settings.local.json`.
- Do not change `LICENSE` or `SECURITY.md` unless asked.
- Do not retarget the GitHub repository used for updates unless asked.
- `assets/wporg/` is the plugins-screen banner and icon.
- New development-only paths must be added to the exclusion list in `bin/build-zip.sh`. That script builds the zip from tracked files.
