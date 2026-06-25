=== Wordnest ===
Contributors: simonxuan
Tags: glossary, tooltip, terms, dictionary, definitions
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A lightweight WordPress glossary tooltip plugin with zero frontend dependencies, native Chinese support, and English term matching.

== Description ==

For detailed documentation, source code, screenshots, issue reports, and development updates, please visit GitHub: https://github.com/Simon-xuan/Wordnest

= English =

**Wordnest** is a minimalist glossary tooltip plugin built for WordPress. It automatically detects terms in your posts or pages and shows their definitions on hover, using a native frontend stack: pure CSS and Vanilla JavaScript, with no jQuery and no third-party frontend dependencies.

Wordnest is designed for documentation sites, course sites, knowledge bases, technical blogs, language-learning content, product documentation, and any WordPress site that needs to explain terms inside article content.

= Key features =

* Automatically adds hover tooltips to terms in posts and pages
* Pure CSS + Vanilla JavaScript frontend, with no jQuery dependency
* Native Chinese support and English term matching
* Central term management through a dedicated custom post type
* Alias support with the `Term｜Alias` title format
* CSV text import for adding many terms at once
* Optional first-occurrence-only highlighting per post
* Skips links, headings, code blocks, scripts, and style areas to avoid breaking content
* Uses WordPress Transient caching to reduce database queries

= Why Wordnest =

Many glossary and tooltip plugins are powerful but load more frontend assets than some sites need. Wordnest focuses on one simple job: explain terms inside content while staying lightweight, predictable, and easy to maintain.

= 中文 =

**Wordnest** 是一款专为 WordPress 打造的极简术语工具提示插件。它会自动识别文章或页面正文中的术语，并在鼠标悬停时显示释义。前端使用纯 CSS + Vanilla JavaScript，无 jQuery、无第三方前端依赖。前身是 LiteGlossary。

它适合用于文档站、课程站、知识库、技术博客、语言学习内容、产品说明页，以及任何需要在正文中解释专业名词的 WordPress 站点。

= 主要功能 =

* 自动识别文章或页面正文中的术语，并添加悬停工具提示
* 纯 CSS + Vanilla JavaScript 前端实现，无 jQuery、无第三方依赖
* 原生中文支持，兼容英文术语匹配
* 通过自定义文章类型集中管理术语和释义
* 支持 `术语｜别名` 形式，让多个名称共享同一释义
* 支持 CSV 文本批量导入术语
* 可选择只高亮每篇文章中首次出现的术语
* 自动跳过已有链接、标题、代码块、脚本和样式区域，尽量避免破坏原文结构
* 使用 WordPress Transient 缓存术语列表，减少数据库查询

= 为什么选择 Wordnest =

许多术语表或 tooltip 插件功能很全，但也会加载较重的前端资源。Wordnest 的目标更简单：只做一件事，把文章里的术语解释清楚，并尽量保持轻量、可控、易维护。

== Installation ==

= English =

1. In your WordPress dashboard, go to Plugins -> Add New.
2. Search for "Wordnest".
3. Install and activate the plugin.
4. After activation, a "Glossary" menu appears in the admin sidebar.
5. Add terms under "Glossary", and configure highlighting or CSV import under Settings -> Wordnest.

= 中文 =

1. 在 WordPress 后台进入“插件” -> “安装插件”。
2. 搜索 “Wordnest”。
3. 点击“安装”，然后启用插件。
4. 启用后，后台侧边栏会出现“词汇表”菜单。
5. 在“词汇表”中添加术语，在“设置 -> Wordnest”中配置高亮方式或批量导入。

== Frequently Asked Questions ==

= Does Wordnest support Chinese and English terms? =

Yes. Wordnest provides native Chinese support and English term matching.

= Will it modify links or headings in my content? =

No. Wordnest skips existing links, headings, code blocks, scripts, and style areas.

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

= 支持中文和英文术语吗？ =

支持。Wordnest 原生支持中文术语，并兼容英文术语匹配。

= 会修改文章中的链接或标题吗？ =

不会。插件会跳过 `<a>` 链接和 `<h1>` 到 `<h6>` 标题，并且不会处理代码块、脚本和样式区域。

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

== Changelog ==

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

= 1.1.2 =

This version migrates the custom post type from `glossary` to `wordnest` to satisfy WordPress.org naming requirements. Existing terms are migrated automatically.
