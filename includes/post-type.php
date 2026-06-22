<?php
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 注册词汇表自定义文章类型
 */
function wordnest_register_post_type() {
    $labels = array(
        'name'                  => _x( '词汇表', 'Post Type General Name', 'wordnest' ),
        'singular_name'         => _x( '词汇表术语', 'Post Type Singular Name', 'wordnest' ),
        'menu_name'             => __( '词汇表', 'wordnest' ),
        'name_admin_bar'        => __( '词汇表术语', 'wordnest' ),
        'archives'              => __( '词汇表归档', 'wordnest' ),
        'attributes'            => __( '词汇表属性', 'wordnest' ),
        'parent_item_colon'     => __( '父词汇表术语:', 'wordnest' ),
        'all_items'             => __( '所有词汇表术语', 'wordnest' ),
        'add_new_item'          => __( '添加新词汇表术语', 'wordnest' ),
        'add_new'               => __( '添加新', 'wordnest' ),
        'new_item'              => __( '新词汇表术语', 'wordnest' ),
        'edit_item'             => __( '编辑词汇表术语', 'wordnest' ),
        'update_item'           => __( '更新词汇表术语', 'wordnest' ),
        'view_item'             => __( '查看词汇表术语', 'wordnest' ),
        'view_items'            => __( '查看词汇表术语', 'wordnest' ),
        'search_items'          => __( '搜索词汇表术语', 'wordnest' ),
        'not_found'             => __( '未找到', 'wordnest' ),
        'not_found_in_trash'    => __( '回收站中未找到', 'wordnest' ),
        'featured_image'        => __( '特色图片', 'wordnest' ),
        'set_featured_image'    => __( '设置特色图片', 'wordnest' ),
        'remove_featured_image' => __( '移除特色图片', 'wordnest' ),
        'use_featured_image'    => __( '用作特色图片', 'wordnest' ),
        'insert_into_item'      => __( '插入到词汇表术语', 'wordnest' ),
        'uploaded_to_this_item' => __( '上传到此词汇表术语', 'wordnest' ),
        'items_list'            => __( '词汇表术语列表', 'wordnest' ),
        'items_list_navigation' => __( '词汇表术语列表导航', 'wordnest' ),
        'filter_items_list'     => __( '筛选词汇表术语列表', 'wordnest' ),
    );
    
    $args = array(
        'label'                 => __( '词汇表术语', 'wordnest' ),
        'description'           => __( '用于显示工具提示的词汇表术语', 'wordnest' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 80,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => array( 'slug' => 'wordnest' ),
    );
    
    register_post_type( 'wordnest', $args );
}
add_action( 'init', 'wordnest_register_post_type' );

/**
 * 一次性数据迁移：把旧 CPT 标识符 glossary 重命名为 wordnest
 *
 * 用 wordnest_db_version 版本门控，只跑一次。直接更新 wp_posts.post_type，
 * 老用户升级后术语不会丢失。改写规则在迁移后刷新一次。
 */
function wordnest_migrate_post_type() {
    $current_db_version = get_option( 'wordnest_db_version', '1.1' );

    if ( version_compare( $current_db_version, '1.2', '<' ) ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->posts,
            array( 'post_type' => 'wordnest' ),
            array( 'post_type' => 'glossary' ),
            array( '%s' ),
            array( '%s' )
        );

        update_option( 'wordnest_db_version', '1.2' );
        flush_rewrite_rules();
    }
}
add_action( 'admin_init', 'wordnest_migrate_post_type' );
