<?php
/**
 * Plugin Name: Wordnest
 * Plugin URI: https://github.com/Simon-xuan/Wordnest
 * Description: 一个轻量级的 WordPress 词汇表插件，为内容中的术语添加工具提示。
 * Version: 1.2.0
 * Requires at least: 5.0
 * Requires PHP: 8.0
 * Author: Simonxuan
 * Author URI: https://github.com/Simon-xuan
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wordnest
 * Domain Path: /languages
 */

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 定义插件常量
define( 'WORDNEST_VERSION', '1.2.0' );
define( 'WORDNEST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WORDNEST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 翻译由 WordPress.org 自 WP 4.6 起自动加载（已删除多余的 load_plugin_textdomain 调用）。

// 加载必要的文件
require_once WORDNEST_PLUGIN_DIR . 'includes/post-type.php';
require_once WORDNEST_PLUGIN_DIR . 'includes/content-filter.php';
require_once WORDNEST_PLUGIN_DIR . 'includes/admin-page.php';

// 加载资源
function wordnest_enqueue_assets() {
    // 仅在单个文章/页面加载
    if ( is_singular() ) {
        // 加载 CSS
        wp_enqueue_style(
            'wordnest-tooltip',
            WORDNEST_PLUGIN_URL . 'assets/css/tooltip.css',
            array(),
            WORDNEST_VERSION
        );
        
        // 加载 JS
        wp_enqueue_script(
            'wordnest-tooltip',
            WORDNEST_PLUGIN_URL . 'assets/js/tooltip.js',
            array(),
            WORDNEST_VERSION,
            true // 在页脚加载
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wordnest_enqueue_assets' );

// 注册激活钩子
function wordnest_activation() {
    // 注册文章类型
    wordnest_register_post_type();
    
    // 刷新重写规则
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wordnest_activation' );

// 注册停用钩子
function wordnest_deactivation() {
    // 刷新重写规则
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wordnest_deactivation' );
