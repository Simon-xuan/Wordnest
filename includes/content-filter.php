<?php
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 将非 ASCII 字符转为数值实体（供 DOMDocument 正确解析 UTF-8）。
 *
 * 优先使用 mbstring 的 mb_encode_numericentity；若主机未安装 mbstring 扩展，
 * 则用纯 PCRE（/u 模式，属 pcre 而非 mbstring）逐个码点回退，避免致命错误。
 */
function wordnest_to_numeric_entities( $str ) {
    if ( function_exists( 'mb_encode_numericentity' ) ) {
        return mb_encode_numericentity( $str, array( 0x80, 0x10FFFF, 0, 0x1FFFFF ), 'UTF-8' );
    }

    return preg_replace_callback(
        '/[\x{0080}-\x{10FFFF}]/u',
        function ( $m ) {
            $char  = $m[0];
            $bytes = array_values( unpack( 'C*', $char ) );
            $n     = count( $bytes );
            if ( 2 === $n ) {
                $cp = ( ( $bytes[0] & 0x1F ) << 6 ) | ( $bytes[1] & 0x3F );
            } elseif ( 3 === $n ) {
                $cp = ( ( $bytes[0] & 0x0F ) << 12 ) | ( ( $bytes[1] & 0x3F ) << 6 ) | ( $bytes[2] & 0x3F );
            } elseif ( 4 === $n ) {
                $cp = ( ( $bytes[0] & 0x07 ) << 18 ) | ( ( $bytes[1] & 0x3F ) << 12 ) | ( ( $bytes[2] & 0x3F ) << 6 ) | ( $bytes[3] & 0x3F );
            } else {
                $cp = $bytes[0];
            }
            return '&#' . $cp . ';';
        },
        $str
    );
}

/**
 * 统计字符串的字符数（码点数），mbstring 不可用时用 PCRE 回退。
 */
function wordnest_char_len( $str ) {
    if ( function_exists( 'mb_strlen' ) ) {
        return mb_strlen( $str );
    }
    return (int) preg_match_all( '/./us', $str );
}

/**
 * 获取缓存版本号。术语或设置变化时刷新该值，使已处理正文缓存失效。
 */
function wordnest_get_cache_version() {
    return (string) get_option( 'wordnest_cache_version', '1' );
}

/**
 * 清除术语缓存，并刷新已处理正文缓存版本。
 */
function wordnest_clear_cache() {
    delete_transient( 'wordnest_terms' );
    update_option( 'wordnest_cache_version', (string) microtime( true ), false );
}

/**
 * 判断当前正文是否适合缓存处理后的输出。
 */
function wordnest_can_cache_content( $post_id ) {
    return $post_id > 0 && ! is_preview();
}

/**
 * 生成按文章和正文内容区分的处理后正文缓存 key。
 */
function wordnest_get_content_cache_key( $post_id, $content, $first_occurrence_only ) {
    $cache_fingerprint = implode(
        '|',
        array(
            absint( $post_id ),
            WORDNEST_VERSION,
            wordnest_get_cache_version(),
            (int) $first_occurrence_only,
            md5( $content ),
        )
    );

    return 'wordnest_content_' . absint( $post_id ) . '_' . md5( $cache_fingerprint );
}

/**
 * 过滤内容，为词汇表术语添加工具提示
 */
function wordnest_filter_content( $content ) {
    // 仅在单个文章/页面过滤
    if ( ! is_singular() ) {
        return $content;
    }
    // 获取插件设置
    $first_occurrence_only = get_option( 'wordnest_first_occurrence_only', false );

    $post_id = get_the_ID();
    if ( ! $post_id ) {
        $post_id = get_queried_object_id();
    }

    $content_cache_key = '';
    if ( wordnest_can_cache_content( (int) $post_id ) ) {
        $content_cache_key = wordnest_get_content_cache_key( (int) $post_id, $content, $first_occurrence_only );
        $cached_content    = get_transient( $content_cache_key );

        if ( false !== $cached_content ) {
            return $cached_content;
        }
    }
    
    // 获取词汇表术语（带缓存）
    $terms = wordnest_get_terms();
    
    if ( empty( $terms ) ) {
        return $content;
    }
    
    // 跟踪已找到的术语（仅首次出现选项）
    $found_terms = array();
    
    // 使用 DOMDocument 解析内容，避免修改链接和标题
    $dom = new DOMDocument();
    
    // 添加文档类型以避免解析问题
    $content = '<!DOCTYPE html><html><body>' . $content . '</body></html>';
    
    // 将非 ASCII 字符转为数值实体，确保中文正确解析
    // （PHP 8.3+ 已移除 mb_convert_encoding 的 'HTML-ENTITIES'，此写法兼容 PHP 7~8.x；
    //   并对未安装 mbstring 扩展的主机提供纯 PCRE 回退，避免致命错误）
    $content = wordnest_to_numeric_entities( $content );

    // 抑制格式错误的 HTML 警告
    @$dom->loadHTML( $content );
    
    // 获取所有文本节点
    $text_nodes = wordnest_get_text_nodes( $dom->getElementsByTagName( 'body' )->item( 0 ) );
    
    // 构建术语标题 -> 释义的映射，并按标题长度降序排列以优先匹配较长术语
    $term_map = array();
    foreach ( $terms as $term ) {
        $term_map[ $term['title'] ] = $term['content'];
    }
    $titles = array_keys( $term_map );
    usort( $titles, function ( $a, $b ) {
        return wordnest_char_len( $b ) - wordnest_char_len( $a );
    } );

    // 单条正则一次性匹配所有术语，支持中文和标点符号的词边界
    // 确保术语不被英文字母包围
    $quoted = array_map(
        function ( $title ) {
            return preg_quote( $title, '/' );
        },
        $titles
    );
    $pattern = '/(?<![a-zA-Z])(' . implode( '|', $quoted ) . ')(?![a-zA-Z])/u';

    // 处理每个文本节点
    //
    // 直接用 DOM 节点构建（createElement / createTextNode / setAttribute），
    // 而不是拼接 HTML 字符串再 appendXML：
    //   1. 周围文本中的 &、< 等字符由 libxml 在序列化时正确转义，不会丢内容；
    //   2. 释义文本不再被当作 preg_replace 的替换串，$0/$1/\ 等元字符不会被解释。
    foreach ( $text_nodes as $node ) {
        $text = $node->nodeValue;

        if ( '' === $text || ! preg_match_all( $pattern, $text, $matches, PREG_OFFSET_CAPTURE ) ) {
            continue;
        }

        $new_nodes = array();
        $last      = 0;
        $did_wrap  = false;

        foreach ( $matches[1] as $hit ) {
            $matched = $hit[0];
            $offset  = $hit[1]; // 字节偏移，与 strlen/substr 一致

            // 如果仅首次出现且术语已找到，则跳过
            if ( $first_occurrence_only && in_array( $matched, $found_terms, true ) ) {
                continue;
            }

            // 匹配项之前的纯文本
            if ( $offset > $last ) {
                $new_nodes[] = $dom->createTextNode( substr( $text, $last, $offset - $last ) );
            }

            // 术语高亮 span：前端脚本负责 hover、focus、tap/click 等显示方式。
            $span = $dom->createElement( 'span' );
            $span->setAttribute( 'class', 'wordnest-term' );
            $span->setAttribute( 'data-tooltip', wp_strip_all_tags( $term_map[ $matched ] ) );
            $span->appendChild( $dom->createTextNode( $matched ) );
            $new_nodes[] = $span;

            $last     = $offset + strlen( $matched );
            $did_wrap = true;

            if ( $first_occurrence_only ) {
                $found_terms[] = $matched;
            }
        }

        // 没有实际包裹任何术语（例如首次出现模式下全被跳过），保持原节点不动
        if ( ! $did_wrap ) {
            continue;
        }

        // 剩余的尾部文本
        if ( $last < strlen( $text ) ) {
            $new_nodes[] = $dom->createTextNode( substr( $text, $last ) );
        }

        // 用新节点序列替换原始文本节点
        $parent = $node->parentNode;
        foreach ( $new_nodes as $new_node ) {
            $parent->insertBefore( $new_node, $node );
        }
        $parent->removeChild( $node );
    }
    
    // 获取修改后的内容
    $body = $dom->getElementsByTagName( 'body' )->item( 0 );
    $modified_content = '';
    
    if ( $body ) {
        foreach ( $body->childNodes as $child ) {
            $modified_content .= $dom->saveHTML( $child );
        }
    }
    if ( $content_cache_key ) {
        set_transient( $content_cache_key, $modified_content, HOUR_IN_SECONDS );
    }

    return $modified_content;
}
add_filter( 'the_content', 'wordnest_filter_content' );

/**
 * 获取词汇表术语（带缓存）
 */
function wordnest_get_terms() {
    // 检查瞬态缓存
    $terms = get_transient( 'wordnest_terms' );
    
    if ( false === $terms ) {
        // 查询数据库获取词汇表术语
        $args = array(
            'post_type'      => 'wordnest',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids', // 仅获取文章 ID 以提高性能
        );
        
        $post_ids = get_posts( $args );
        
        $terms = array();
        
        if ( ! empty( $post_ids ) ) {
            foreach ( $post_ids as $post_id ) {
                $term_title = get_the_title( $post_id );
                $term_content = get_post_field( 'post_content', $post_id );
                $term_permalink = get_permalink( $post_id );
                
                // 分割多个术语（格式：术语1｜术语2）
                $terms_list = explode( '｜', $term_title );
                
                foreach ( $terms_list as $single_term ) {
                    $single_term = trim( $single_term );
                    if ( ! empty( $single_term ) ) {
                        // 处理换行过多的问题
                        $clean_content = wp_strip_all_tags( $term_content );
                        $clean_content = preg_replace( '/\s+/', ' ', $clean_content ); // 将多个连续空白字符替换为单个空格
                        
                        $terms[] = array(
                            'title'      => $single_term,
                            'content'    => $clean_content,
                            'permalink'  => $term_permalink,
                            'post_id'    => $post_id,
                        );
                    }
                }
            }
        }
        
        // 存储在瞬态缓存中，有效期 1 小时
        set_transient( 'wordnest_terms', $terms, HOUR_IN_SECONDS );
    }
    
    return $terms;
}

/**
 * 当词汇表文章更新时清除缓存
 */
function wordnest_clear_cache_on_term_change( $post_id ) {
    if ( get_post_type( $post_id ) === 'wordnest' ) {
        wordnest_clear_cache();
    }
}
add_action( 'save_post', 'wordnest_clear_cache_on_term_change' );
add_action( 'delete_post', 'wordnest_clear_cache_on_term_change' );

/**
 * 递归获取所有文本节点，排除不应改写的区域
 *
 * 跳过：链接、标题，以及代码/预格式化/脚本/样式/表单文本等区域——
 * 否则代码示例（<pre>/<code>）、内联脚本或样式里的文本会被术语高亮污染。
 */
function wordnest_get_text_nodes( $node, &$text_nodes = array() ) {
    $skip_tags = array(
        'a',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'pre', 'code', 'kbd', 'samp', 'var', 'tt',
        'script', 'style', 'textarea', 'noscript',
    );
    if ( in_array( strtolower( $node->nodeName ), $skip_tags, true ) ) {
        return $text_nodes;
    }
    
    // 处理子节点
    if ( $node->hasChildNodes() ) {
        foreach ( $node->childNodes as $child ) {
            if ( $child->nodeType === XML_TEXT_NODE && trim( $child->nodeValue ) !== '' ) {
                $text_nodes[] = $child;
            } else {
                wordnest_get_text_nodes( $child, $text_nodes );
            }
        }
    }
    
    return $text_nodes;
}

/**
 * 注册插件设置
 */
function wordnest_register_settings() {
    add_option( 'wordnest_first_occurrence_only', false );
    register_setting(
        'wordnest_settings',
        'wordnest_first_occurrence_only',
        array(
            'type'              => 'boolean',
            'sanitize_callback' => 'wordnest_sanitize_first_occurrence',
            'default'           => false,
        )
    );
}
add_action( 'admin_init', 'wordnest_register_settings' );

/**
 * 净化「仅高亮首次出现」设置（复选框，统一返回 1 或 0）
 */
function wordnest_sanitize_first_occurrence( $value ) {
    return ! empty( $value ) ? 1 : 0;
}
