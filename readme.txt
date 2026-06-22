=== Wordnest ===
Contributors: simonxuan
Tags: glossary, tooltip, terms, dictionary, definitions
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lightweight glossary plugin that shows a tooltip when readers hover a term. Zero dependencies, first-class CJK support.

== Description ==

**Wordnest** is a minimalist tooltip plugin for WordPress. Instead of bloated alternatives, it uses a native stack (pure CSS + Vanilla JavaScript) so readers can hover over a term in your content and instantly see its definition — fast, lightweight, and dependency-free.

**Features**

* **Dedicated admin panel** — manage all your terms centrally via a custom post type.
* **Smart matching** — automatically detects terms in content while skipping existing links and headings, so your layout stays intact.
* **First-occurrence mode** — optionally highlight only the first occurrence of each term per post.
* **Native front-end** — pure CSS + Vanilla JavaScript, no jQuery, no extra libraries.
* **Bulk import** — import large term lists at once from plain CSV text.
* **High performance** — built-in Transient caching reduces database queries.
* **First-class CJK support** — regex matching and storage are optimized for Chinese and other multibyte text.
* **Fully internationalized** — ships with Simplified Chinese (default) and English; the interface follows your site language.

The plugin parses content with `DOMDocument` and only touches plain text nodes, so it never breaks your links or headings.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wordnest` directory, or install the plugin through the **Plugins** screen in WordPress.
2. Activate the plugin through the **Plugins** screen.
3. Go to **Glossary → Add New Glossary Term** to add terms, or use **Settings → Wordnest → Import** to bulk-import from CSV.

== Frequently Asked Questions ==

= Does it support Chinese / CJK terms? =

Yes, fully. Both the regex matching (`/u` modifier) and database storage are optimized for Chinese and other multibyte characters.

= How do I add a term with aliases? =

In the term title, separate aliases with a full-width vertical bar, e.g. `TV｜Television`. Both will match the same definition.

= How do I customize the tooltip's appearance? =

Edit `assets/css/tooltip.css` in the plugin directory to change the bubble background, font size, corner radius, etc.

= My bulk import isn't working. What should I check? =

Make sure the separator comma is a half-width `,` and that every line follows the `Term,Definition` format.

= Will it affect links or headings in my content? =

No. The plugin parses content with `DOMDocument` and automatically skips `<a>` links and `<h1>`–`<h6>` headings, processing only plain text.

= Activation fails with a fatal error. What now? =

This usually means two copies of the plugin are installed (for example an old copy left in another folder). The duplicate functions cause a `Cannot redeclare function` error. Keep only one copy of the plugin and activate again.

== Screenshots ==

1. Front-end tooltip — hover a term to reveal its definition.
2. Add a single glossary term in the admin.
3. Bulk-import terms from CSV.
4. Manage all glossary terms.

== Changelog ==

= 1.2.0 =
* Compliance: prefixed the custom post type identifier with the plugin name (`glossary` → `wordnest`) to satisfy the WordPress.org review requirement that CPT/taxonomy identifiers be uniquely named and conflict-free.
* Migration: added a one-time, version-gated upgrade routine that renames existing `glossary` posts to `wordnest` in the database, so current users keep all their terms after upgrading. Rewrite rules are flushed once after the rename.

= 1.1.1 =
* Compliance: added nonce verification to the post-redirect admin notice flags, and moved the CSV-import nonce check into the import handler's own scope — clears the WordPress.org Plugin Check NonceVerification warnings.

= 1.1.0 =
Renamed to **Wordnest**, plus a security/robustness pass.

Naming:
* Renamed the plugin to Wordnest; the text domain and slug are now `wordnest`.

Security:
* Every translated string echoed into HTML is now escaped (`esc_html_e` / `esc_html__` / `esc_attr_e` / `esc_js`), and `wp_nonce_url()` output is wrapped in `esc_url()`.
* Added an `if ( ! defined( 'ABSPATH' ) ) exit;` guard to every executable PHP file.
* Added explicit capability checks (`current_user_can( 'manage_options' )`) to the settings-save and CSV-import handlers.

Bug fixes:
* Fixed content loss on the front end: a paragraph containing a raw ampersand (e.g. "AT&T", "R&D") alongside a matched term could be dropped entirely. Term highlighting is now built with native DOM nodes instead of string concatenation + `appendXML()`.
* Fixed tooltip corruption when a term definition contained `$0`, `${1}`, or a backslash (these were interpreted as regex back-references).
* CSV import now matches existing draft/pending/private/scheduled terms, so re-importing a term updates it instead of creating a duplicate.
* Term matching now skips `<pre>`, `<code>`, `<script>`, `<style>` (and `kbd`/`samp`/`var`/`textarea`) regions, so code samples and embedded content are no longer altered.

Robustness:
* Added pure-PCRE fallbacks for the `mbstring` functions (`mb_encode_numericentity` / `mb_strlen` / `mb_substr`), so the plugin no longer fatals on hosts without the mbstring extension.

Standards / compliance:
* Admin JavaScript now loads via `wp_enqueue_script()` on the settings page instead of inline `<script>` tags.
* Removed the global `ob_start()`; admin form/delete handling moved to `admin_init` so redirects work without output buffering.
* Removed the redundant `load_plugin_textdomain()` call (WordPress.org loads translations automatically since WP 4.6).
* The settings page is now a submenu under **Settings** instead of a high-priority top-level menu.

= 1.0.2 =
* Security: the tooltip is now built with DOM text nodes instead of innerHTML, removing the XSS sink flagged by code scanning (js/xss-through-dom).

= 1.0.1 =
* Security: all input is unslashed and sanitized; nonces are sanitized before verification; an explicit capability check was added to single-term deletion; CSV import sanitizes each field.
* Fix: the "Only highlight the first occurrence" setting is now saved correctly.
* Performance: removed leftover code that cleared the term cache on every request — Transient caching now actually takes effect.
* Naming: the name shown in the Plugins list is now "Lite Glossary".
* Compliance: added a sanitize_callback to register_setting; passes the WordPress.org Plugin Check.

= 1.0.0 =
* Initial release: core term-matching engine, CSV bulk import with Transient caching, Vanilla JS tooltips with zero front-end dependencies.

== Upgrade Notice ==

= 1.1.1 =
Adds nonce verification to satisfy the WordPress.org Plugin Check. No functional changes.

= 1.1.0 =
Renamed to Wordnest, with output-escaping and security hardening throughout, proper script enqueuing, and a tidier Settings submenu. Recommended for all users.

= 1.0.2 =
Security fix: removes a DOM XSS sink in the tooltip script (flagged by code scanning). Recommended for all users.

= 1.0.1 =
Security hardening, a settings-save fix, real caching, and WordPress.org Plugin Check compliance. Recommended for all users.
