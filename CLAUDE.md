# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**Mission News** — a classic (non-block) WordPress theme by Compete Themes, distributed
on WordPress.org under GPL v2. It's a newspaper/magazine theme. The canonical version
number is the `Version:` header of `style.css` (currently 1.65) — bump it there when
releasing (note `readme.txt`'s `Stable tag` can lag behind).

## Build / development

CSS and JS ship compiled. **Never hand-edit the generated files** — they are overwritten
by the build:
- generated CSS: `style.css`, `style.min.css`, `rtl.css`, `rtl.min.css`, and everything
  in `styles/` (`customizer*`, `admin*`, `editor-style*`)
- generated JS: everything in `js/build/`

Edit the SCSS in `sass/` and the JS sources in `js/` (`fitvids.js`, `functions.js`,
`postMessage.js`) instead, then run Grunt.

```bash
npm install     # grunt 0.4.x toolchain (note: old; may need an older node)
npx grunt       # NOTE: the default task starts `watch` early and blocks — see below
npx grunt sass autoprefixer cssmin cssjanus   # one-shot CSS rebuild
npx grunt concat uglify                        # one-shot JS rebuild
npx grunt makepot                              # regenerate languages/mission-news.pot
```

Pipeline (`gruntfile.js`):
- **CSS:** `sass/style.scss` → `style.css`; `sass/customizer.scss`, `sass/admin.scss`,
  `sass/editor_style.scss` → `styles/`. Then `autoprefixer` (in place) → `cssmin`
  (`*.min.css`). `cssjanus` generates `rtl.css` from `style.css` (RTL is generated, not
  hand-maintained).
- **JS:** `concat` bundles `js/fitvids.js` + `js/functions.js` → `js/build/production.js`,
  then `uglify` → `js/build/production.min.js` (and `js/postMessage.js` →
  `postMessage.min.js`).

There is no test suite. The `shell:zip` task contains the original author's hardcoded
local Dropbox paths — it won't work here; ignore it.

## Architecture

Classic WordPress template hierarchy, kept deliberately minimal: there is **no
`single.php` / `archive.php` / `page.php`**. `index.php` is the loop entry point for
nearly every view; `search.php` and `404.php` are the only other request-level templates.
Inside the loop, `ct_mission_news_get_content_template()` (in `functions.php`) is the
central dispatcher — it inspects context + the `layout` / `layout_first_image` theme mods
and calls `get_template_part()` to pull the right partial:
- **`content*.php`** — single-post (`content.php`) and the four archive/home layout
  variants (`content-archive.php`, `content-archive-rows.php`,
  `content-archive-rows-excerpt.php`, `content-archive-double.php`), plus
  `content-page.php`, `content-attachment.php`.
- **`content/*.php`** — smaller post sub-parts (`post-author.php`, `post-categories.php`,
  `post-tags.php`, `archive-header.php`, `comments-link.php`, `search-bar.php`, etc.)
  included by the `content*.php` templates.
- **`sidebar-*.php`** — one file per registered widget area. The theme exposes many:
  `left`, `right`, `below-header`, `above-main`, `after-post`, `after-first-post`,
  `after-page`, `site-footer` (registered in `ct_mission_news_register_widget_areas` in
  `functions.php`). Sidebar IDs in `register_sidebar()` must match the `dynamic_sidebar()`
  call in the corresponding `sidebar-*.php`.
- **`header.php` / `footer.php` / `menu-*.php` / `logo.php` / `comments.php`** — chrome.

`functions.php` (~1100 lines) is the hub: theme setup/`add_theme_support`, widget-area
registration, the content dispatcher, byline/excerpt/featured-image helpers, and dynamic
CSS output (`ct_mission_news_site_width_css`, `ct_mission_news_widget_styles`). At the top
it `require_once`s everything under `inc/`:
- `inc/scripts.php` — **all** front-end script/style enqueueing (not `functions.php`)
- `inc/customizer.php` — Customizer sections/settings/controls (the theme is configured
  through the Customizer + `get_theme_mod()`, not a custom settings page)
- `inc/meta-box-layout.php`, `inc/meta-box-fi-display.php`,
  `inc/last-updated-meta-box.php` — per-post editor meta boxes (layout override, featured
  image display, last-updated date)
- `inc/social-icons.php` — social-icon SVG output helper (`ct_mission_news_svg_output`);
  general iconography is Font Awesome, bundled under `assets/font-awesome/`
- `inc/comments.php` — comment rendering callback
- `inc/widgets/post-list.php` — the bundled "Recent Posts Extended" widget

`theme-options.php` is a Compete Themes upsell/dashboard page (admin only), not functional
config. `tgm/class-tgm-plugin-activation.php` is the vendored TGM Plugin Activation
library; `ct_mission_news_register_required_plugins()` in `functions.php` uses it to
recommend the Independent Analytics plugin. Integrations have dedicated templates:
`woocommerce.php`, `bbpress.php` (+ matching `sass/_woocommerce.scss`, `_bbpress.scss`).

## Conventions

- **Prefix everything `ct_mission_news_`** (PHP functions) and `ct-mission-news-` /
  `mission-news-` (enqueue handles, CSS classes). Wrap function definitions in
  `if ( ! function_exists( ... ) )` as the existing code does (allows child-theme
  overrides).
- **Text domain `mission-news`** on every user-facing string; translations live in
  `languages/` (`.pot`/`.po`/`.mo`), regenerated via `grunt makepot`.
- **SCSS:** `sass/style.scss` is an import manifest; real rules live in
  `sass/_*.scss` partials (per section: `_header`, `_post`, `_layouts`, `_widgets`,
  `_woocommerce`, …). Add a partial and `@import` it from the manifest.
- **Asset URLs** via `get_template_directory_uri()` / `get_stylesheet_uri()` — never
  hardcode paths.
- **Settings** are read with `get_theme_mod()` (Customizer), with sanitize callbacks
  defined in `inc/customizer.php`.
