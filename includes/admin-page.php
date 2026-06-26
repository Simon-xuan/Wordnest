<?php
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 添加管理菜单
 *
 * 作为「设置」下的子菜单，避免顶级菜单挤占 WordPress 核心导航位置。
 */
function wordnest_add_admin_menu() {
    $hook = add_options_page(
        __( '轻量级词汇表设置', 'wordnest' ),
        __( '轻量级词汇表', 'wordnest' ),
        'manage_options',
        'wordnest',
        'wordnest_admin_page'
    );

    // 记录本页面的 hook，供 admin_enqueue_scripts 精准加载脚本。
    $GLOBALS['wordnest_admin_page_hook'] = $hook;
}
add_action( 'admin_menu', 'wordnest_add_admin_menu' );

/**
 * 通过标准 enqueue 机制加载后台脚本（仅限本插件设置页）。
 */
function wordnest_admin_enqueue_scripts( $hook ) {
    if ( empty( $GLOBALS['wordnest_admin_page_hook'] ) || $hook !== $GLOBALS['wordnest_admin_page_hook'] ) {
        return;
    }

    wp_enqueue_script(
        'wordnest-admin',
        WORDNEST_PLUGIN_URL . 'assets/js/admin.js',
        array(),
        WORDNEST_VERSION,
        true
    );
}
add_action( 'admin_enqueue_scripts', 'wordnest_admin_enqueue_scripts' );

/**
 * 处理表单与删除操作。
 *
 * 挂在 admin_init 上，在任何页面输出之前执行，因此 wp_safe_redirect()
 * 无需依赖输出缓冲即可正常工作。
 */
function wordnest_handle_admin_actions() {
    // 处理单个删除操作
    if ( isset( $_GET['page'], $_GET['action'], $_GET['term_id'] )
        && 'wordnest' === $_GET['page']
        && 'wordnest_delete' === $_GET['action'] ) {
        // 验证 nonce（先 unslash + sanitize，避免直接信任输入）
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( wp_verify_nonce( $nonce, 'wordnest_delete' ) ) {
            // 权限检查（纵深防御，与批量删除一致）
            if ( ! current_user_can( 'delete_posts' ) ) {
                wp_die( esc_html__( '你没有权限执行此操作', 'wordnest' ) );
            }
            $term_id = intval( $_GET['term_id'] );
            if ( $term_id > 0 && wp_delete_post( $term_id, true ) ) {
                wordnest_clear_cache();
                // 重定向回管理页面并显示成功消息
                wp_safe_redirect( add_query_arg( array(
                    'page'           => 'wordnest',
                    'single_deleted' => 1,
                    '_wpnonce'       => wp_create_nonce( 'wordnest_notice' ),
                ), admin_url( 'options-general.php' ) ) );
                exit;
            }
        } else {
            wp_die( esc_html__( '安全验证失败', 'wordnest' ) );
        }
    }

    // 处理批量删除操作
    if ( isset( $_POST['wordnest_bulk_delete'] ) ) {
        // 1. 安全检查：验证 Nonce 随机数，防止 CSRF 攻击
        if ( ! isset( $_POST['wordnest_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wordnest_nonce'] ) ), 'wordnest_bulk_delete' ) ) {
            wp_die( esc_html__( '安全验证失败', 'wordnest' ) );
        }

        // 2. 权限检查：确保当前用户有删除权限
        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_die( esc_html__( '你没有权限执行此操作', 'wordnest' ) );
        }

        // 3. 获取 ID 并执行删除
        if ( ! empty( $_POST['term_ids'] ) && is_array( $_POST['term_ids'] ) ) {
            // 先 unslash 再用 array_map(intval) 处理 ID，确保数据全是数字
            $term_ids = array_map( 'intval', (array) wp_unslash( $_POST['term_ids'] ) );

            $deleted = 0;
            foreach ( $term_ids as $term_id ) {
                if ( $term_id > 0 && wp_delete_post( $term_id, true ) ) {
                    $deleted++;
                }
            }

            // 清除缓存
            wordnest_clear_cache();

            // 4. 操作完成后重定向并添加反馈参数
            wp_safe_redirect( add_query_arg( array(
                'page'         => 'wordnest',
                'bulk_deleted' => $deleted,
                '_wpnonce'     => wp_create_nonce( 'wordnest_notice' ),
            ), admin_url( 'options-general.php' ) ) );
            exit;
        } else {
            // 没有选择术语，重定向回管理页面
            wp_safe_redirect( add_query_arg( array(
                'page'              => 'wordnest',
                'no_terms_selected' => 1,
                '_wpnonce'          => wp_create_nonce( 'wordnest_notice' ),
            ), admin_url( 'options-general.php' ) ) );
            exit;
        }
    }

    // 处理 CSV 导入（nonce 校验放在 wordnest_handle_csv_import 内，与 $_POST 读取同作用域）
    if ( isset( $_POST['wordnest_import'], $_POST['wordnest_csv'] ) && current_user_can( 'manage_options' ) ) {
        wordnest_handle_csv_import();
    }

    // 保存设置并清除缓存
    if ( isset( $_POST['wordnest_settings'] ) ) {
        if ( current_user_can( 'manage_options' )
            && isset( $_POST['wordnest_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wordnest_nonce'] ) ), 'wordnest_settings' ) ) {
            // 复选框未勾选时 $_POST 中不存在该字段，按"关闭"处理
            $first_only = ! empty( $_POST['wordnest_first_occurrence_only'] ) ? 1 : 0;
            update_option( 'wordnest_first_occurrence_only', $first_only );
            wordnest_clear_cache();
        }
    }
}
add_action( 'admin_init', 'wordnest_handle_admin_actions' );

/**
 * 管理页面回调函数
 */
function wordnest_admin_page() {
    // 显示操作反馈提示（实际处理在 wordnest_handle_admin_actions 中完成）。
    // 这些 GET 标志由本插件重定向时附带的 nonce 保护，校验通过后才读取/显示，
    // 既满足 nonce 规范，也防止他人伪造「已删除 N 条」之类的提示。
    $wordnest_notice_nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
    if ( $wordnest_notice_nonce && wp_verify_nonce( $wordnest_notice_nonce, 'wordnest_notice' ) ) {
        if ( isset( $_GET['bulk_deleted'] ) && intval( $_GET['bulk_deleted'] ) > 0 ) {
            /* translators: %d: number of glossary terms deleted */
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( __( '成功删除 %d 个术语。', 'wordnest' ), intval( $_GET['bulk_deleted'] ) ) ) . '</p></div>';
        } elseif ( isset( $_GET['single_deleted'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '术语已成功删除。', 'wordnest' ) . '</p></div>';
        } elseif ( isset( $_GET['no_terms_selected'] ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '请选择要删除的术语。', 'wordnest' ) . '</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( '轻量级词汇表设置', 'wordnest' ); ?></h1>

        <h2 class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active"><?php esc_html_e( '设置', 'wordnest' ); ?></a>
            <a href="#import" class="nav-tab"><?php esc_html_e( '导入', 'wordnest' ); ?></a>
            <a href="#manage" class="nav-tab"><?php esc_html_e( '术语管理', 'wordnest' ); ?></a>
        </h2>

        <div id="settings" class="tab-content">
            <form method="post" action="">
                <?php wp_nonce_field( 'wordnest_settings', 'wordnest_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( '高亮选项', 'wordnest' ); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="wordnest_first_occurrence_only" value="1" <?php checked( get_option( 'wordnest_first_occurrence_only', false ), 1 ); ?>>
                                <?php esc_html_e( '仅高亮内容中首次出现的术语', 'wordnest' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="wordnest_settings" value="1">
                <?php submit_button( __( '保存设置', 'wordnest' ) ); ?>
            </form>
        </div>

        <div id="import" class="tab-content" style="display: none;">
            <h2><?php esc_html_e( '导入词汇表术语', 'wordnest' ); ?></h2>
            <p><?php esc_html_e( '在下方粘贴 CSV 文本（格式：术语1｜术语2,解释）。已存在的术语将被更新。', 'wordnest' ); ?></p>

            <form method="post" action="">
                <?php wp_nonce_field( 'wordnest_import', 'wordnest_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'CSV 文本', 'wordnest' ); ?>
                        </th>
                        <td>
                            <textarea name="wordnest_csv" rows="10" cols="50" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( '示例：ABC｜张三,张三 - 一班\nDEF｜李四,李四 - 二班', 'wordnest' ); ?></p>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="wordnest_import" value="1">
                <?php submit_button( __( '导入术语', 'wordnest' ) ); ?>
            </form>
        </div>

        <div id="manage" class="tab-content" style="display: none;">
            <h2><?php esc_html_e( '术语管理', 'wordnest' ); ?></h2>
            <p><?php esc_html_e( '以下是所有已添加的词汇表术语，您可以编辑或删除它们。', 'wordnest' ); ?></p>

            <div class="tablenav top">
                <div class="alignleft actions">
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wordnest' ) ); ?>" class="button button-primary"><?php esc_html_e( '新增术语', 'wordnest' ); ?></a>
                </div>
                <br class="clear">
            </div>

            <?php
            // 获取所有词汇表术语
            $args = array(
                'post_type'      => 'wordnest',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            );

            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                ?>
                <form method="post" action="" id="terms-form">
                    <?php wp_nonce_field( 'wordnest_bulk_delete', 'wordnest_nonce' ); ?>
                    <div class="tablenav top">
                        <div class="alignleft actions">
                            <input type="submit" name="wordnest_bulk_delete" class="button button-danger" value="<?php esc_attr_e( '批量删除', 'wordnest' ); ?>" onclick="return confirm('<?php echo esc_js( __( '确定要删除选中的术语吗？', 'wordnest' ) ); ?>');">
                        </div>
                        <br class="clear">
                    </div>
                    <table class="widefat fixed" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-cb check-column">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th scope="col" class="manage-column column-title"><?php esc_html_e( '术语', 'wordnest' ); ?></th>
                                <th scope="col" class="manage-column column-content"><?php esc_html_e( '解释', 'wordnest' ); ?></th>
                                <th scope="col" class="manage-column column-actions"><?php esc_html_e( '操作', 'wordnest' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ( $query->have_posts() ) {
                                $query->the_post();
                                $term_id = get_the_ID();
                                $term_title = get_the_title();
                                $term_content = wp_strip_all_tags( get_the_content() );
                                $term_content = wordnest_truncate_chars( $term_content, 50 );
                                ?>
                                <tr>
                                    <td class="check-column">
                                        <input type="checkbox" name="term_ids[]" value="<?php echo esc_attr( $term_id ); ?>">
                                    </td>
                                    <td class="column-title">
                                        <strong><?php echo esc_html( $term_title ); ?></strong>
                                    </td>
                                    <td class="column-content">
                                        <?php echo esc_html( $term_content ); ?>
                                    </td>
                                    <td class="column-actions">
                                        <a href="<?php echo esc_url( get_edit_post_link( $term_id ) ); ?>" class="button button-primary"><?php esc_html_e( '编辑', 'wordnest' ); ?></a>
                                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'wordnest_delete', 'term_id' => $term_id ), admin_url( 'options-general.php?page=wordnest' ) ), 'wordnest_delete' ) ); ?>" class="button button-danger" onclick="return confirm('<?php echo esc_js( __( '确定要删除这个术语吗？', 'wordnest' ) ); ?>');"><?php esc_html_e( '删除', 'wordnest' ); ?></a>
                                    </td>
                                </tr>
                                <?php
                            }
                            wp_reset_postdata();
                            ?>
                        </tbody>
                    </table>
                </form>
                <?php
            } else {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php esc_html_e( '还没有添加任何词汇表术语。', 'wordnest' ); ?></p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * 处理 CSV 导入
 */
function wordnest_handle_csv_import() {
    // 校验 nonce（与下方 $_POST 读取处于同一函数作用域，满足 nonce 规范）。
    check_admin_referer( 'wordnest_import', 'wordnest_nonce' );

    if ( ! isset( $_POST['wordnest_csv'] ) ) {
        return;
    }

    $csv_text = trim( sanitize_textarea_field( wp_unslash( $_POST['wordnest_csv'] ) ) );

    if ( empty( $csv_text ) ) {
        return;
    }

    // 将 CSV 文本分割成行
    $lines = explode( "\n", $csv_text );

    $imported = 0;
    $updated = 0;

    foreach ( $lines as $line ) {
        $line = trim( $line );

        if ( empty( $line ) ) {
            continue;
        }

        // 将行分割成部分
        $parts = explode( ',', $line, 2 );

        if ( count( $parts ) < 2 ) {
            continue;
        }

        $abbreviation = sanitize_text_field( trim( $parts[0] ) );
        $full_name = sanitize_text_field( trim( $parts[1] ) );

        if ( empty( $abbreviation ) || empty( $full_name ) ) {
            continue;
        }

        // 检查术语是否已存在
        $existing_post = wordnest_get_existing_term( $abbreviation );

        if ( $existing_post ) {
            // 更新现有术语
            $post_id = wp_update_post( array(
                'ID'           => $existing_post->ID,
                'post_title'   => $abbreviation,
                'post_content' => $full_name,
                'post_type'    => 'wordnest',
                'post_status'  => 'publish',
            ) );

            if ( $post_id ) {
                $updated++;
            }
        } else {
            // 创建新术语
            $post_id = wp_insert_post( array(
                'post_title'   => $abbreviation,
                'post_content' => $full_name,
                'post_type'    => 'wordnest',
                'post_status'  => 'publish',
            ) );

            if ( $post_id ) {
                $imported++;
            }
        }
    }

    // 清除缓存
    wordnest_clear_cache();

    // 显示通知
    if ( $imported > 0 || $updated > 0 ) {
        $message = sprintf(
            /* translators: 1: number of new terms imported, 2: number of existing terms updated */
            __( '导入完成：%1$d 个新术语，%2$d 个更新术语', 'wordnest' ),
            $imported,
            $updated
        );

        add_action( 'admin_notices', function() use ( $message ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
            <?php
        });
    }
}

/**
 * 按字符数截断字符串（mbstring 不可用时用 PCRE 回退，避免致命错误）。
 */
function wordnest_truncate_chars( $str, $len ) {
    if ( function_exists( 'mb_strlen' ) ) {
        return mb_strlen( $str ) > $len ? mb_substr( $str, 0, $len ) . '...' : $str;
    }
    $chars = preg_split( '//u', $str, -1, PREG_SPLIT_NO_EMPTY );
    return count( $chars ) > $len ? implode( '', array_slice( $chars, 0, $len ) ) . '...' : $str;
}

/**
 * 根据标题获取现有词汇表术语
 *
 * 涵盖草稿/待审/私密/定时等状态，避免导入时把同名草稿术语当成"不存在"
 * 而重复新建（仅排除回收站与自动草稿）。
 */
function wordnest_get_existing_term( $title ) {
    $args = array(
        'post_type'      => 'wordnest',
        'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
        'posts_per_page' => 1,
        'title'          => $title,
        'fields'         => 'ids', // 仅获取文章 ID 以提高性能
    );

    $post_ids = get_posts( $args );

    if ( ! empty( $post_ids ) ) {
        return get_post( $post_ids[0] );
    }

    return false;
}
