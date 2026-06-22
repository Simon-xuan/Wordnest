<div align="center">

# 📖 Wordnest

**A minimalist WordPress plugin that adds term tooltips to your post content**

Zero dependencies · First-class CJK support · Smart matching · High-performance caching

[简体中文](README.md) · **English**

<br>

[![Version](https://img.shields.io/badge/version-1.1.0-2ea44f?style=for-the-badge)](https://github.com/Simon-xuan/Wordnest/releases)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-21759b?style=for-the-badge&logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.0%2B-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL--2.0%2B-blue?style=for-the-badge)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![Dependencies](https://img.shields.io/badge/dependencies-none-success?style=for-the-badge)](#-why-wordnest)

[Features](#-features) ·
[Install](#-installation) ·
[Usage](#-usage) ·
[FAQ](#-faq) ·
[License](#-license)

</div>

---

> **Wordnest** is a minimalist tooltip plugin built for WordPress. Using a native stack (pure CSS + Vanilla JS) instead of bloated alternatives, it lets readers hover over a term in your content and instantly see its definition — fast, lightweight, dependency-free.Formerly known as Lite-Glossary.

<div align="center">

<!-- Screenshot 1: front-end tooltip (hover a term to reveal its definition) -->
<img width="720" alt="Front-end tooltip demo" src="docs/01-tooltip-demo.gif" />

</div>

---

## ✨ Features

| Feature | Description |
| :--- | :--- |
| 🧩 **Dedicated admin panel** | Manage all your terms centrally via a custom post type (CPT) |
| 🎯 **Smart matching** | Automatically detects terms in content while skipping existing links `<a>` and headings `<h1>`–`<h6>`, so layout stays intact |
| 🥇 **First-occurrence mode** | Optionally highlight only the first occurrence of each term per post, keeping pages clean |
| ⚡ **Native front-end** | Pure CSS + Vanilla JavaScript, zero dependencies (no jQuery), no extra libraries loaded |
| 📥 **Bulk import** | Import large term lists at once from plain CSV text |
| 🚀 **High performance** | Built-in Transient caching dramatically reduces database queries |
| 🀄 **First-class English and Chinese support** | Regex matching and storage are optimized for Chinese and other multibyte text — no garbled output, no false matches |
| 🔗 **Familiar structure** | Uses a data structure similar to CM Tooltip, so it *may* be compatible (untested — verify on your own) |

---

## 🤔 Why Wordnest

- **Light** — just a few PHP files plus one CSS/JS file. Install and go.
- **Fast** — no third-party libraries on the front end, terms are cached, near-zero overhead.
- **Safe** — parses content with `DOMDocument` and only touches plain text nodes, never breaking links or headings.
- **Effortless** — native CJK support, CSV bulk import, everything managed in one place.

---

## 🚀 Installation

1. **Download** the latest `Wordnest.zip` from the [Releases](https://github.com/Simon-xuan/Wordnest/releases) page.
2. **Upload** it in your WordPress dashboard → **Plugins** → **Add New Plugin** → **Upload Plugin**.
3. **Activate** the plugin. A "Glossary" menu appears in the sidebar, and the plugin's own settings live under **Settings → Wordnest**.

---

## 📖 Usage

### 1️⃣ Add a single term

Go to **Glossary → Add New Glossary Term**:

- **Title field**: the term name. Aliases are supported — separate them with a full-width vertical bar `｜`.
- **Content field**: the term's definition (i.e. the tooltip text).

| Title syntax | Matching behavior |
| :--- | :--- |
| `Phone` | Shows its definition wherever "Phone" appears |
| `TV｜Television` | Matches either "TV" or "Television" with the same definition |

<div align="center">

<!-- Screenshot 2: "Add New Glossary Term" editor -->
<img width="720" alt="Add term screen" src="docs/02-add-term.png" />

</div>

### 2️⃣ Bulk import terms

On the **Settings → Wordnest → Import** tab, paste CSV text — one term per line, in the format `Term[｜Alias],Definition`:

```text
ABC｜John,John - Class 1
DEF｜Jane,Jane - Class 2
Phone,A mobile phone
TV｜Television,A television set
```

> 💡 Existing terms with the same name are **updated automatically** rather than duplicated.

<div align="center">

<!-- Screenshot 3: the "Import" tab -->
<img width="720" alt="Bulk import screen" src="docs/03-import.png" />

</div>

### 3️⃣ Settings & management

- **Settings** tab: tick "Only highlight the first occurrence of each term" to enable first-occurrence mode.
- **Manage Terms** tab: view, edit, delete individual terms, or bulk-delete them all.

<div align="center">

<!-- Screenshot 4: glossary term list / management -->
<img width="720" alt="Term list and management" src="docs/04-manage.png" />

</div>

---

## 🎨 Customizing the appearance

The tooltip styles live in `assets/css/tooltip.css`. Edit the bubble's background color, font size, corner radius, etc. directly:

```css
.wordnest-tooltip {
    background-color: #333;   /* bubble background */
    color: #fff;              /* text color */
    border-radius: 4px;       /* corner radius */
    font-size: 14px;          /* font size */
}
```

---

## ❓ FAQ

<details>
<summary><strong>Does it support Chinese / CJK terms?</strong></summary>

Yes, fully. Both the regex matching (`/u` modifier) and database storage are optimized for Chinese and other multibyte characters.
</details>

<details>
<summary><strong>How do I customize the tooltip's appearance?</strong></summary>

Edit `assets/css/tooltip.css` in the plugin directory. See [Customizing the appearance](#-customizing-the-appearance).
</details>

<details>
<summary><strong>Bulk import isn't working — what should I check?</strong></summary>

Make sure the separator comma is a **half-width** `,` and that every line follows the `Term,Definition` format.
</details>

<details>
<summary><strong>Will it affect links or headings in my content?</strong></summary>

No. The plugin parses content with `DOMDocument` and automatically skips `<a>` links and `<h1>`–`<h6>` headings, processing only plain text.
</details>

<details>
<summary><strong>Activation fails with "The plugin could not be activated because it triggered a fatal error" — what now?</strong></summary>

This usually means **two copies of the plugin are installed** — e.g. an old copy left behind in another folder under `wp-content/plugins/` when you uploaded a new version. The two copies declare the same functions, so WordPress hits a `Cannot redeclare function ...` fatal error on activation.

**Fix**: In Dashboard → Plugins, keep only **one** copy of "Wordnest". If unsure, use FTP / a file manager to check `wp-content/plugins/` for duplicate folders (e.g. `wordnest` alongside `lite-glossary` or `wordnest-old`), delete the extras, then activate.
</details>

---

## 🌍 Internationalization (i18n)

The plugin is fully internationalized, and **the interface language automatically follows your WordPress site language** (Dashboard → Settings → General → Site Language). No manual switch needed.

| Language | Status |
| :--- | :--- |
| Simplified Chinese | ✅ Built-in default |
| English | ✅ Translation provided (`languages/wordnest-en_US.mo`) |

- When the site language is **English**, the UI shows English; for **Simplified Chinese** or anything else, it shows Chinese.
- Want to add another language? Use `languages/wordnest.pot` as the template, save your translation as `wordnest-{locale}.po` (replace `{locale}` with the target language's WordPress locale code), compile it to `.mo` with `msgfmt`, drop it into `languages/` — or submit a PR.

---

## 📂 Project structure

```text
wordnest/
├── wordnest.php          # Entry point: constants, asset loading, activation/deactivation hooks
├── includes/
│   ├── post-type.php          # Registers the "Glossary" custom post type
│   ├── content-filter.php     # Term matching, tooltip injection, caching
│   └── admin-page.php         # Admin settings / import / term management
├── assets/
│   ├── css/tooltip.css        # Tooltip styles
│   └── js/tooltip.js          # Vanilla JS hover logic
├── languages/                 # Translations (.pot template + en_US)
└── .github/workflows/         # Manual build & release workflow
```

---

## 🛠 Changelog

### v1.2.0

- Compliance: prefixed the custom post type (CPT) identifier with the plugin name (`glossary` → `wordnest`) to satisfy the WordPress.org review requirement that CPT/taxonomy identifiers be uniquely named and conflict-free
- Migration: added a one-time, version-gated upgrade routine that renames existing `glossary` posts to `wordnest` in the database, so existing users keep all terms after upgrading; rewrite rules are flushed once after the rename

### v1.1.1

- Compliance: added nonce verification to the post-redirect admin notice flags, and moved the CSV-import nonce check into the import handler's own scope — clears the WordPress.org Plugin Check NonceVerification warnings. No functional changes.

### v1.1.0

Renamed to **Wordnest**, plus a security and robustness pass.

**Naming**
- The plugin is now Wordnest; the text domain and slug are both `wordnest`

**Security**
- Every translated string echoed into HTML is now escaped (`esc_html_e` / `esc_html__` / `esc_attr_e` / `esc_js`), and `wp_nonce_url()` output is wrapped in `esc_url()`
- Added an `if ( ! defined( 'ABSPATH' ) ) exit;` guard to every executable PHP file
- Added explicit capability checks (`current_user_can( 'manage_options' )`) to the settings-save and CSV-import handlers

**Bug fixes**
- Fixed front-end content loss: a paragraph with a raw ampersand (e.g. "AT&T", "R&D") next to a matched term could be dropped entirely; term highlighting is now built with native DOM nodes instead of string concatenation + `appendXML()`
- Fixed tooltip corruption when a definition contained `$0`, `${1}`, or a backslash (interpreted as regex back-references)
- CSV import now matches existing draft/pending/private/scheduled terms, so re-importing a term updates it instead of creating a duplicate
- Term matching now skips `<pre>`, `<code>`, `<script>`, `<style>` (and `kbd`/`samp`/`var`/`textarea`) regions, so code samples and embedded content are left untouched

**Robustness**
- Added pure-PCRE fallbacks for the `mbstring` functions (`mb_encode_numericentity` / `mb_strlen` / `mb_substr`), so the plugin no longer fatals on hosts without the mbstring extension

**Standards / compliance**
- Admin JavaScript now loads via `wp_enqueue_script()` instead of inline `<script>`
- Removed the global `ob_start()` and moved form/delete handling to `admin_init`
- Removed the redundant `load_plugin_textdomain()` (WordPress.org loads translations automatically since WP 4.6)
- The settings page is now a submenu under **Settings**

### v1.0.2

- Security: the tooltip is now rendered with DOM text nodes + `<br>` elements instead of `innerHTML`, eliminating the XSS sink flagged by code scanning (`js/xss-through-dom`)

### v1.0.1

- Security hardening: all input unslashed + sanitized, nonces sanitized before verification, capability check added to single-term deletion
- Fix: the "Only highlight the first occurrence" setting now saves correctly
- Performance: removed leftover migration code that cleared the cache on every request — Transient caching now actually takes effect
- Naming: the name shown in the Plugins list is now "Lite Glossary"
- Compliance: added a `sanitize_callback` to `register_setting`; the production zip excludes extra Markdown files (passes the WordPress.org Plugin Check)
- Packaging: release zip uses a proper top-level folder and excludes doc assets

### v1.0.0

- Core term-matching engine
- CSV bulk import with Transient caching
- Vanilla JS tooltips, zero front-end dependencies

---

## 📄 License

Released under the [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.txt) license.

<div align="center">

If this project helps you, please consider leaving a ⭐ Star!

**This project was built with AI assistance.**

</div>
