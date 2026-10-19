# Project context

_Shared, committed memory. Keep it factual and current. If the repo is public, so is this file._

- **Product:** Magic Page Query Helper, a WordPress plugin. Owner: Odell Duppins Jr / Duppins Technology. Author header: Duppins Technology.
- **Repo:** https://github.com/duppinstech/magic-page-query-helper (public)
- **Current release:** v1.0.1 (2026-10-18)
- **Distribution:** GitHub Releases. `includes/class-mpqh-github-updater.php` installs `magic-page-query-helper.zip` for `duppinstech/magic-page-query-helper`.
- **Key dependencies:** none. No `composer.json` and no `package.json`.
- **Integrations:** GitHub Releases API. The plugin reads the existing `location` taxonomy.
- **CI:** lint on PHP 7.4 and 8.2; releases built by `release.yml` on `v*` tags. No test suite.

## Review watchlist
Known items each department re-checks on every review until fixed. Remove lines when they're resolved.
- **Security:** none yet
- **Legal:** none yet
- **Finance:** none yet
- **Product:** none yet
- **Marketing:** none yet
- **Support:** none yet
- **Ops:** `uninstall.php` deletes the `mpqh_github_release` site option and leaves the `mpqh_last_request` option.
- **UX:** none yet
