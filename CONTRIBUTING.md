# Contributing

## Branches

Branch from `main`.

- `feature/<short-name>` for new behavior
- `fix/<short-name>` for bug fixes
- `chore/<short-name>` for docs, CI, and repo hygiene

Open a pull request into `main`. Do not commit directly to `main`.

## Commits

Use an imperative conventional subject:

```text
type: summary
```

Types used here: `feat`, `fix`, `docs`, `ci`, `chore`.

## Pull requests

CI runs on the pull request: PHP syntax lint, then `bin/release-notes.sh` for the version in the plugin header. Both must pass.

Describe what changed and how you checked it. Fill in the pull request template.

## Releases

When the version changes, keep these four the same:

- plugin header `Version`
- `MPQH_VERSION`
- `Stable tag` in `readme.txt`
- git tag `v` plus that version, on `main`

Add `## [x.y.z]` to `CHANGELOG.md` before tagging. `bin/release-notes.sh` reads that section when `CHANGELOG.md` exists. The heading must not contain "Unreleased", and the notes must not be empty. Keep the same notes under `== Changelog ==` in `readme.txt`.

Tagging `v*` on `main` runs `.github/workflows/release.yml`, which publishes `magic-page-query-helper.zip` to a GitHub Release.

## Secrets

Do not commit `.env`, `.env.*`, `CLAUDE.local.md`, or `.claude/settings.local.json`.
