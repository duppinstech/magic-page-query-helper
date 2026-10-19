# Business context

_Committed. If the repo is public, keep only public-safe facts here: published tiers and list prices, positioning, cost drivers. Private numbers go in the "Business (private)" section of the gitignored `CLAUDE.local.md`._

Read by `finance-reviewer`, `marketing-reviewer`, `legal-reviewer` and `product-reviewer`. Items marked _TBD_ are open questions for Odell. Agents must not fill them in by guessing.

## Product and positioning
- **Product:** Magic Page Query Helper, by Duppins Technology (https://duppinstech.com).
- **Audience:** WordPress sites that use Magic Page, or a similar location-page generator, with a very large `location` taxonomy.
- **Positioning:** Loads location term data in bulk so later lookups are not separate database queries. Does not modify Magic Page's files, and does not replace a full-page cache. Nothing to configure. Settings → MPQH Dashboard is a status screen.
- **Built for (client):** product work

## Tiers
| Tier | What's included | Price | Distribution |
|---|---|---|---|
| Core | Bulk location-term loading, Settings → MPQH Dashboard, plugins-screen details, and GitHub updates | No price | GitHub Releases (`magic-page-query-helper.zip`) |

## Cost drivers
- No paid third-party API. The updater calls the public GitHub Releases API. A GitHub token in the updater config is optional.
- Hosting: not applicable
- Support time: none
