<?php
add_action('parse_query', function (&$wp_query){
	if(!is_admin() && $wp_query->get('post_type') == 'nav_menu_item'){	// 让菜单也支持缓存
		$wp_query->set('suppress_filters', false);
	}
});

add_filter('posts_pre_query', function ($pre, $wp_query){
	if($wp_query->get('orderby') == 'rand'){	// 随机排序就不能缓存了
		return $pre;
	}

	if(!$wp_query->is_main_query() && $wp_query->get('post_type') != 'nav_menu_item' && !$wp_query->get('cache_it') ){	// 只缓存主循环 || 菜单 || 要求缓存的
		return $pre;
	}

	$cache_key	= 'wpjam_cache:'.md5(maybe_serialize($wp_query->query_vars)).':'.wp_cache_get_last_changed('posts');
	$post_ids	= wp_cache_get($cache_key, 'wpjam_post_ids');

	$wp_query->set('cache_key', $cache_key);
	
	if($post_ids === false){
		return $pre;
	}

	if(!$wp_query->is_singular() && empty($wp_query->get('nopaging')) && empty($wp_query->get('no_found_rows'))){	// 如果需要缓存总数
		$found_posts	= wp_cache_get($cache_key, 'wpjam_found_posts');;

		if($found_posts === false){
			return $pre;
		}

		$wp_query->set('no_found_rows', true);

		$wp_query->found_posts		= $found_posts;
		$wp_query->max_num_pages	= ceil($found_posts/$wp_query->get('posts_per_page'));
	}

	$args	= wp_array_slice_assoc($wp_query->query_vars, ['update_post_term_cache', 'update_post_meta_cache']);

	return wpjam_get_posts($post_ids, $args);	
}, 10, 2); 

add_filter('posts_results',	 function ($posts, $wp_query) {
	$cache_key	= $wp_query->get('cache_key');

	if($cache_key){
		$post_ids	= wp_cache_get($cache_key, 'wpjam_post_ids');
		if($post_ids === false){
			wp_cache_set($cache_key, array_column($posts, 'ID'), 'wpjam_post_ids', HOUR_IN_SECONDS);
		}
	}

	return $posts;
}, 10, 2);

add_filter('found_posts', function ($found_posts, $wp_query) {
	$cache_key	= $wp_query->get('cache_key');

	if($cache_key){
		wp_cache_set($cache_key, $found_posts, 'wpjam_found_posts', HOUR_IN_SECONDS);
	}
		
	return $found_posts;
}, 10, 2);
