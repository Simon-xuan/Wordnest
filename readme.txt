=== Wordnest ===
Contributors: simonxuan
Tags: glossary, tooltip, terms, dictionary, definitions
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A lightweight WordPress glossary tooltip plugin with zero frontend dependencies, native Chinese support, and English term matching.

== Description ==

详情请查看：GitHub https://github.com/Simon-xuan/Wordnest

= English =

**Wordnest** is a minimalist glossary tooltip plugin built for WordPress. It automatically detects terms in your posts or pages and shows their definitions on hover, keyboard focus, or tap/click.

The frontend is intentionally small: pure CSS plus Vanilla JavaScript, with no jQuery and no third-party frontend dependencies. Wordnest is designed for documentation sites, course sites, knowledge bases, technical blogs, language-learning content, product documentation, and any WordPress site that needs to explain terms inside article content.

= Features =

* Dedicated admin panel: manage all terms centrally through a custom post type.
* Smart matching: automatically detects terms in post/page content while skipping links, headings, code blocks, scripts, styles, and form text areas.
* Accessible tooltips: supports hover, keyboard focus, tap/click, outside click dismissal, blur dismissal, and Esc dismissal.
* First-occurrence mode: optionally highlight only the first occurrence of each term per post.
* Native frontend: pure CSS + Vanilla JavaScript, zero frontend dependencies, no jQuery.
* Bulk import: paste CSV text to add or update many terms at once.
* Alias support: use the `Term｜Alias` title format so multiple names share one definition.
* High performance: caches both the glossary term list and processed post output with WordPress Transients.
* Native Chinese support and English term matching.

= Why Wordnest =

Many glossary and tooltip plugins are powerful, but they often load more frontend assets than small documentation or education sites need. Wordnest focuses on one job: explain terms inside content while staying lightweight, predictable, and easy to maintain.

Wordnest parses content with `DOMDocument` and rewrites text nodes instead of running a raw regex over HTML. This helps avoid corrupting tags, attributes, links, headings, and code examples.

= Usage =

1. Add a single term

Go to **Glossary -> Add New Glossary Term**.

* Title field: enter the term name. Aliases are supported with the full-width vertical bar `｜`.
* Content field: enter the term definition. This becomes the tooltip text.

Examples:

`
Phone
TV｜Television
`

2. Bulk import terms

Go to **Settings -> Wordnest -> Import** and paste CSV text, one term per line:

`
ABC｜John,John - Class 1
DEF｜Jane,Jane - Class 2
Phone,A mobile phone
TV｜Television,A television set
`

Existing terms with the same title are updated automatically rather than duplicated.

3. Settings and management

* Settings tab: enable "Only highlight the first occurrence of each term" if you want cleaner long-form pages.
* Manage Terms tab: view, edit, delete individual terms, or bulk-delete selected terms.

= Customizing the appearance =

Tooltip styles live in `assets/css/tooltip.css`. You can edit the bubble background, text color, font size, spacing, border radius, focus outline, and responsive behavior there.

Example:

`
.wordnest-tooltip {
    background-color: #333;
    color: #fff;
    border-radius: 4px;
    font-size: 14px;
}
`

= Internationalization =

Wordnest is fully internationalized. The interface language follows your WordPress site language automatically.

* Simplified Chinese: built-in default.
* English: translation provided in `languages/wordnest-en_US.mo`.

To add another language, use `languages/wordnest.pot` as the template, save the translation as `wordnest-{locale}.po`, compile it to `.mo`, and place it in `languages/`.

= Chinese =

**Wordnest** 是一款专为 WordPress 打造的极简术语工具提示插件。它会自动识别文章或页面正文中的术语，并在鼠标悬停、键盘聚焦或触摸点击时显示释义。

前端使用纯 CSS + Vanilla JavaScript，无 jQuery、无第三方前端依赖。Wordnest 适合文档站、课程站、知识库、技术博客、语言学习内容、产品说明页，以及任何需要在正文中解释专业名词的 WordPress 站点。

= 功能特点 =

* 专用管理面板：通过自定义文章类型集中管理所有术语。
* 智能匹配算法：自动识别正文术语，并跳过链接、标题、代码块、脚本、样式和表单文本区域。
* 可访问工具提示：支持悬停、键盘聚焦、触摸点击、外部点击关闭、失焦关闭与 Esc 关闭。
* 首词高亮模式：可选择只高亮每篇文章中首次出现的术语，让长文章更清爽。
* 原生轻量前端：纯 CSS + Vanilla JavaScript，零前端依赖，无 jQuery。
* 批量导入：支持粘贴 CSV 文本，一次添加或更新大量术语。
* 别名支持：使用 `术语｜别名` 标题格式，让多个名称共享同一释义。
* 高性能缓存：使用 WordPress Transient 缓存术语列表和处理后正文。
* 原生中文支持，兼容英文术语匹配。

= 为什么选择 Wordnest =

很多术语表或 tooltip 插件功能很全，但也会加载较重的前端资源。Wordnest 的目标更简单：只做一件事，把文章里的术语解释清楚，并尽量保持轻量、可控、易维护。

Wordnest 使用 `DOMDocument` 解析正文并改写文本节点，而不是直接对原始 HTML 跑正则。这能尽量避免破坏标签、属性、链接、标题和代码示例。

= 使用指南 =

1. 添加单个术语

进入 **词汇表 -> 添加新术语**。

* 标题栏：填写术语名称，支持用全角竖线 `｜` 分隔别名。
* 正文栏：填写该术语释义，也就是工具提示内容。

示例：

`
Phone
TV｜Television
`

2. 批量导入术语

进入 **设置 -> 轻量级词汇表 -> 导入**，粘贴 CSV 文本，每行一条：

`
ABC｜张三,张三 - 一班
DEF｜李四,李四 - 二班
Phone,手机
TV｜Television,电视
`

已存在的同名术语会自动更新，不会重复创建。

3. 设置与管理

* 设置标签页：勾选“仅高亮内容中首次出现的术语”即可开启首词高亮模式。
* 术语管理标签页：查看、编辑、单条删除或批量删除术语。

= 自定义外观 =

工具提示样式位于 `assets/css/tooltip.css`。你可以在这里调整气泡背景色、文字颜色、字号、间距、圆角、键盘焦点样式和响应式表现。

示例：

`
.wordnest-tooltip {
    background-color: #333;
    color: #fff;
    border-radius: 4px;
    font-size: 14px;
}
`

= 多语言 =

Wordnest 已完整国际化，界面语言会自动跟随 WordPress 站点语言。

* 简体中文：默认内置。
* English：已提供 `languages/wordnest-en_US.mo`。

如需新增其他语言，可用 `languages/wordnest.pot` 作为模板，翻译后命名为 `wordnest-{locale}.po`，编译成 `.mo` 后放入 `languages/`。

== Installation ==

= English =

1. In your WordPress dashboard, go to Plugins -> Add New.
2. Search for "Wordnest".
3. Install and activate the plugin.
4. After activation, a "Glossary" menu appears in the admin sidebar.
5. Add terms under "Glossary", and configure highlighting or CSV import under Settings -> Wordnest.

= Chinese =

1. 在 WordPress 后台进入“插件” -> “安装插件”。
2. 搜索 “Wordnest”。
3. 点击“安装”，然后启用插件。
4. 启用后，后台侧边栏会出现“词汇表”菜单。
5. 在“词汇表”中添加术语，在“设置 -> Wordnest”中配置高亮方式或批量导入。

== Frequently Asked Questions ==

= Does Wordnest support Chinese and English terms? =

Yes. Wordnest provides native Chinese support and English term matching.

= Does it work on touch screens and with keyboards? =

Yes. Tooltips work on hover, keyboard focus, and tap/click. They can be dismissed with Esc, blur, or outside click.

= Will it modify links, headings, or code blocks? =

No. Wordnest skips existing links, headings, code blocks, scripts, style areas, and form text areas.

= Can I bulk import terms? =

Yes. Paste CSV text into the import area on the settings page. Example:

`
Phone,A mobile phone
TV｜Television,A television set
`

= Can I highlight only the first occurrence of each term? =

Yes. Enable the first-occurrence-only option on the settings page.

= Can I customize the tooltip style? =

Yes. The tooltip styles live in `assets/css/tooltip.css`.

= Activation fails with a fatal error. What should I check? =

This usually means two copies of the plugin are installed under `wp-content/plugins/`. Keep only one copy of Wordnest, remove old duplicate folders such as `lite-glossary` or `wordnest-old`, then activate again.

= 支持中文和英文术语吗？ =

支持。Wordnest 原生支持中文术语，并兼容英文术语匹配。

= 触摸屏和键盘能用吗？ =

可以。工具提示支持悬停、键盘聚焦和触摸点击，也支持 Esc、失焦或外部点击关闭。

= 会修改文章中的链接、标题或代码块吗？ =

不会。插件会跳过链接、标题、代码块、脚本、样式区域和表单文本区域。

= 可以批量导入术语吗？ =

可以。在设置页的导入区域粘贴 CSV 文本即可。格式示例：

`
Phone,手机
TV｜Television,电视
`

= 可以只高亮第一次出现的术语吗？ =

可以。在设置页启用“仅高亮内容中首次出现的术语”即可。

= 可以自定义 tooltip 样式吗？ =

可以。样式位于插件目录的 `assets/css/tooltip.css`。

= 启用时报致命错误怎么办？ =

这通常是站点里装了两份本插件。请确认 `wp-content/plugins/` 下只保留一份 Wordnest，删除旧的重复目录，例如 `lite-glossary` 或 `wordnest-old`，然后再启用。

== Screenshots ==

1. Front-end tooltip demo.
2. Add a glossary term.
3. Import terms from CSV text.
4. Manage glossary terms.

== Changelog ==

= 1.1.3 =

* Accessibility: tooltips now support hover, keyboard focus, tap/click, outside click dismissal, blur dismissal, and Esc dismissal.
* Accessibility: added ARIA state, tooltip roles, and visible keyboard focus styling.
* Performance: added per-post processed content caching so DOM parsing and term rewriting are not repeated on every page load.
* Listing: added WordPress.org screenshot descriptions for the existing plugin screenshots.

= 1.1.2 =

* Compliance: prefixed the custom post type identifier with the plugin name (`glossary` -> `wordnest`) for WordPress.org review requirements.
* Migration: added a version-gated upgrade routine so existing glossary terms are migrated to the new `wordnest` post type.

= 1.1.1 =

* Compliance: added nonce verification improvements for admin notice flags and CSV import handling.

= 1.1.0 =

* Renamed the plugin to Wordnest.
* Added security and escaping improvements.
* Improved content parsing robustness and tooltip rendering.
* Added fallback handling for hosts without the mbstring extension.

= 1.0.0 =

* Initial release with term matching, CSV import, caching, and Vanilla JS tooltips.

== Upgrade Notice ==

= 1.1.3 =

Tooltips now work better for keyboard and touch users, and processed post output is cached for better performance.

= 1.1.2 =

This version migrates the custom post type from `glossary` to `wordnest` to satisfy WordPress.org naming requirements. Existing terms are migrated automatically.
