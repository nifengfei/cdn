<?php
function wpjam_theme_get_setting($setting_name){
	return wpjam_get_setting('wpjam_theme', $setting_name);
}

//在文章内容的第二段后面插入广告
if(wpjam_theme_get_setting('single_middle_ad')){
    add_filter( 'the_content', 'prefix_insert_post_ads' );
}
function prefix_insert_post_ads( $content ) {
    $ad_code = '<p style="text-indent:0;">'.wpjam_theme_get_setting('single_middle_ad').'</p>';
    $middle_ad_number = wpjam_theme_get_setting('single_middle_ad_number') ?: '4';
    if ( is_singular('post') && ! is_admin() ) {
        return prefix_insert_after_paragraph( $ad_code, $middle_ad_number, $content );
    }
    return $content;
}
function prefix_insert_after_paragraph( $insertion, $paragraph_id, $content ) {
    $closing_p = '</p>';
    $paragraphs = explode( $closing_p, $content );
    foreach ($paragraphs as $index => $paragraph) {
        if ( trim( $paragraph ) ) {
            $paragraphs[$index] .= $closing_p;
        }
        if ( $paragraph_id == $index + 1 ) {
            $paragraphs[$index] .= $insertion;
        }
    }
    return implode( '', $paragraphs );
}

//面包屑导航
function get_breadcrumbs()  {
    global $wp_query;
    if ( !is_home() ){
        // Start the UL
        //echo '<ul class="breadcrumb">'; 
        echo '<i class="iconfont icon-locationfill"></i> ';
        // Add the Home link  
        echo '<a href="'. get_option('home') .'">首页</a>';

        if ( is_category() )  {
            $catTitle = single_cat_title( "", false );
            $cat = get_cat_ID( $catTitle );
            echo " <span>&raquo;</span> ". get_category_parents( $cat, TRUE, " <span>&raquo;</span> " ) ."";
        }
        elseif ( is_tag() )  {
            echo " <span>&raquo;</span> ".single_cat_title($prefix,$display)."";
        }
        elseif ( is_archive() && !is_category() )  {
            echo " <span>&raquo;</span> Archives";
        }
        elseif ( is_search() ) {
            echo ' <span>&raquo;</span> 搜索结果（共搜索到 ' . $wp_query->found_posts . ' 篇文章）';
        }
        elseif ( is_404() )  {
            echo " <span>&raquo;</span> 404 Not Found";
        }
        elseif ( is_single() )  {
            $category = get_the_category();
            if($category){
                $category_id = get_cat_ID( $category[0]->cat_name );
                echo ' <span>&raquo;</span> '. get_category_parents( $category_id, TRUE, "  <span>&raquo;</span> " );
                echo get_the_title(); 
            }
        }
        elseif ( is_page() )  {
            $post = $wp_query->get_queried_object();
            if ( $post->post_parent == 0 ){
                echo " <span>&raquo;</span> ".the_title('','', FALSE)."";
            } else {
                $title = the_title('','', FALSE);
                $ancestors = array_reverse( get_post_ancestors( $post->ID ) );
                array_push($ancestors, $post->ID);
    
                foreach ( $ancestors as $ancestor ){
                    if( $ancestor != end($ancestors) ){
                        echo ' <span>&raquo;</span> <a href="'. get_permalink($ancestor) .'">'. strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ) .'</a>'; 
                    } else {
                        echo ' <span>&raquo;</span> '. strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ) .'';
                    }
                }
            }
        }
        // End the UL
        //echo "</ul>";
    }
}