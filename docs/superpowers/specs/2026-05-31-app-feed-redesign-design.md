# News Mash — "Vivid App-style Feed" Redesign (Implementation Spec)

Source design: `designs/new-design-1.pen` (frame "App Feed — Homepage", id `oEwt1`).
Target: the Mission News WordPress theme at repo root. Branch `redesign/app-feed`.

## Goal & guardrails

Reskin the front end to the warm-neutral, image-forward, app-style feed without
touching backend mechanisms. **Hard rules:**

- No changes to PHP that alter widget areas, the loop, theme mods, or category
  placement. The theme's content wiring stays 1:1. PHP edits are limited to:
  the font enqueue (`inc/scripts.php`) and small, additive class hooks if a
  selector genuinely cannot be reached in CSS (flag these, don't assume).
- All visual work happens in **new SCSS partials under `sass/redesign/`**, imported
  LAST in `sass/style.scss` so they override legacy rules via the cascade. Do not
  edit existing `sass/_*.scss` partials. Never edit generated CSS
  (`style.css`, `style.min.css`, `styles/*`).
- The mockup's "AI Briefing" / "Trending" / "Latest" cards are **existing sidebar
  widgets restyled as cards** — implemented as generic `.widget` card styling, not
  new widget areas.

## Build / verify

```bash
npx --yes sass@1.77.8 sass/style.scss style.css --no-source-map
npx --yes sass@1.77.8 sass/style.scss style.min.css --style=compressed --no-source-map
```
(Legacy `/`-division deprecation warnings are expected and harmless.)

## Design tokens (from the .pen)

| token            | value     | role                              |
|------------------|-----------|-----------------------------------|
| `--nm-bg`        | `#F6F1E9` | page background (warm cream)       |
| `--nm-surface`   | `#FFFFFF` | card surface                      |
| `--nm-surface-2` | `#FBF7F0` | subtle panel / input              |
| `--nm-ink`       | `#211C16` | primary text, dark footer bg      |
| `--nm-body`      | `#524B40` | body text                         |
| `--nm-muted`     | `#8E8576` | meta / secondary text             |
| `--nm-rule`      | `#E8E0D2` | hairline borders                  |
| `--nm-accent`    | `#C75D3A` | terracotta accent                 |
| `--nm-accent-soft`| `#F3E2D6`| accent tint (pills, read-time)    |

Category accent colors (chips/labels): Politics `#B5543A`, World `#2F726B`,
Tech `#5E5687`, Science `#6B7547`, Health `#AC5A66`, Financial `#B07F2A`,
US `#3F6FA0`, Crime `#7A4A45`.

Radii: cards 14–18px, pills/chips full (999px), thumbnails 10px.
Fonts: headings **Plus Jakarta Sans** (700/800), body **Inter** (400/600).
Define tokens as CSS custom properties on `:root` in `_tokens.scss` so all
partials reference `var(--nm-*)`.

## Mapping mockup → existing markup (confirmed by scout)

- **Header** `.site-header` / `.top-nav` / `.title-container` / `.site-title`,
  primary nav `.menu-primary .menu-primary-items` (`header.php`, `logo.php`,
  `menu-primary.php`). Mockup: white bar, wordmark + "AI-DRIVEN" pill, rounded
  search, social. Nav becomes a horizontal **chip row** (category pills); active
  item = filled terracotta.
- **Below-header ad**: `get_sidebar('below-header')` → style its `.widget` as the
  rounded leaderboard placeholder.
- **Layout**: `.content-container > .layout-container` with `#main.main`,
  `get_sidebar('left')`, `get_sidebar('right')`. Make `.layout-container` a flex/grid
  row: left rail, center, right rail; cream gaps between cards.
- **Archive posts**: `content-archive-rows.php` / `content-archive.php` →
  `.post > article` with `ct_mission_news_featured_image()`, `.post-title`,
  `.post-byline`, `.post-content`. Style as rounded white cards with image-top.
  Top story (`content-archive`, first post) = large hero card.
- **Sidebars**: `.widget` / `.widget-title` (left & right) → white rounded cards
  with the accent rule under the title; ranked lists get big accent numerals.
- **Footer** `.site-footer` (`footer.php`): dark `--nm-ink` bg, columns, social.

## Work breakdown (one partial per agent — no shared files)

All partials live in `sass/redesign/` and are `@import`ed (already scaffolded) in
this order after the legacy imports:

1. `_tokens.scss` — `:root` custom props + a `%nm-card` placeholder + category-color
   map/mixin. (DONE by lead — others depend on it; do not edit.)
2. `_base.scss` — page bg, container widths, base type (Jakarta/Inter), link colors.
3. `_header.scss` — white header bar, wordmark + AI pill, rounded search, social.
4. `_nav-chips.scss` — primary menu restyled as a horizontal chip row + active state.
5. `_layout.scss` — `.layout-container` 3-rail flex/grid + responsive collapse.
6. `_cards.scss` — archive post cards, hero (first) card, image radius, meta, read-time pill.
7. `_widgets.scss` — sidebar widgets as cards, ranked lists w/ numerals, ad slots, trending tags.
8. `_footer.scss` — dark footer, columns, social row.

Each agent: edit ONLY its partial, reference `var(--nm-*)`, scope selectors to real
theme classes (verify against the PHP), keep changes override-only, no `!important`
unless unavoidable. Mobile: rails stack to one column under `$eight-hundred` (50em).

## Acceptance

- `npx sass` compiles `style.css` + `style.min.css` with no errors.
- Homepage renders: chip nav, hero card, 2-up card grid, card sidebars, dark footer,
  warm cream background — matching `new-design-1.pen`.
- No widget area / loop / theme-mod regressions; original PHP behavior intact.
