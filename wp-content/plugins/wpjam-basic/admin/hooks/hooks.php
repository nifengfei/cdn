<?php
add_action('admin_page_access_denied', function(){
	if((is_multisite() && is_user_member_of_blog(get_current_user_id(), get_current_blog_id())) || !is_multisite()){
		wp_die(__( 'Sorry, you are not allowed to access this page.' ).'<a href="'.admin_url().'">返回首页</a>', 403);
	}
});

add_filter('is_protected_meta', function($protected, $meta_key){
	return $protected ?: in_array($meta_key, ['views', 'favs']);
}, 10, 2);

add_filter('removable_query_args', function($removable_query_args){
	return array_merge($removable_query_args, ['added', 'duplicated', 'unapproved',	'unpublished', 'published', 'geted', 'created', 'synced']);
});

add_filter('admin_title', function($admin_title){
	return str_replace(' &#8212; WordPress', '', $admin_title);
});

add_filter('register_post_type_args', function($args, $post_type){
	if(!empty($args['_builtin']) || empty($args['show_ui'])){
		return $args;
	}

	add_filter("post_type_labels_".$post_type, function($labels) use($args){
		$labels		= (array)$labels;
		$name		= $labels['name'];
		$_labels	= $args['labels'] ?? [];
		$search		= empty($args['hierarchical']) ? ['文章', 'post', 'Post', '撰写新', '写'] : ['页面', 'page', 'Page', '撰写新', '写'];
		$replace	= [$name, $name, ucfirst($name), '新建', '新建'];

		foreach ($labels as $key => &$label) {
			if($label && empty($_labels[$key])){
				if($key == 'all_items'){
					$label	= '所有'.$name;
				}elseif($label != $name){
					$label	= str_replace($search, $replace, $label);
				}
			}
		}

		return $labels;
	});

	return $args;
}, 10, 2);

add_filter('register_taxonomy_args', function($args, $taxonomy){
	if(!empty($args['_builtin']) || empty($args['show_ui'])){
		return $args;
	}

	add_filter('taxonomy_labels_'.$taxonomy, function($labels) use($args){
		$labels		= (array)$labels;
		$name		= $labels['name'];
		$_labels	= $args['labels'] ?? [];

		if(empty($args['hierarchical'])){
			$search		= ['标签', 'Tag', 'tag'];
			$replace	= [$name, ucfirst($name), $name];
		}else{
			$search		= ['目录', '分类', 'categories', 'Categories', 'Category'];
			$replace	= ['', $name, $name, $name.'s', ucfirst($name).'s', ucfirst($name)];
		}

		foreach ($labels as $key => &$label) {
			if($label && empty($_labels[$key]) && $label != $name){
				$label	= str_replace($search, $replace, $label);
			}
		}

		return $labels;
	});

	return $args;
}, 10, 2);

add_filter('default_option_wpjam-basic', 'wpjam_basic_get_default_settings');

if(wpjam_basic_get_setting('disable_auto_update')){
	remove_action('admin_init', '_maybe_update_core');
	remove_action('admin_init', '_maybe_update_plugins');
	remove_action('admin_init', '_maybe_update_themes');
}

if(wpjam_basic_get_setting('remove_help_tabs')){  
	add_action('in_admin_header', function(){
		global $current_screen;
		$current_screen->remove_help_tabs();
	});
}

if(wpjam_basic_get_setting('remove_screen_options')){  
	add_filter('screen_options_show_screen', '__return_false');
	add_filter('hidden_columns', '__return_empty_array');
}

if(wpjam_basic_get_setting('disable_privacy')){
	add_action('admin_menu', function(){
		remove_submenu_page('options-general.php', 'options-privacy.php');
		remove_submenu_page('tools.php', 'export-personal-data.php');
		remove_submenu_page('tools.php', 'erase-personal-data.php');
	},11);

	add_action('admin_init', function(){
		remove_action( 'admin_init', array( 'WP_Privacy_Policy_Content', 'text_change_check' ), 100 );
		remove_action( 'edit_form_after_title', array( 'WP_Privacy_Policy_Content', 'notice' ) );
		remove_action( 'admin_init', array( 'WP_Privacy_Policy_Content', 'add_suggested_content' ), 1 );
		remove_action( 'post_updated', array( 'WP_Privacy_Policy_Content', '_policy_page_updated' ) );
		remove_filter( 'list_pages', '_wp_privacy_settings_filter_draft_page_titles', 10, 2 );
	},1);
}

if(wpjam_basic_get_setting('timestamp_file_name')){
	// 防止重名造成大量的 SQL 请求
	add_filter('wp_handle_sideload_prefilter', function($file){
		$file['name']	= time().'-'.$file['name'];
		return $file;
	});

	add_filter('wp_handle_upload_prefilter', function($file){
		$file['name']	= time().'-'.$file['name'];
		return $file;
	});
}

// WordPress 国内镜像
if(wpjam_basic_get_setting('wordpress_mirror')){
	add_filter('site_transient_update_core', function($value){
		if(isset($value->updates)){
			foreach ($value->updates as &$update) {
				if($update->locale == 'zh_CN'){
					$update->download		= 'https://www.xintheme.com/go/wordpress';
					$update->packages->full	= 'https://www.xintheme.com/go/wordpress';
				}
			}
		}

		return $value;
	});

	add_filter('site_transient_update_plugins', function($value){
		if(isset($value->response) && isset($value->response['wpjam-basic/wpjam-basic.php'])){
			$value->response['wpjam-basic/wpjam-basic.php']->package	= 'https://www.xintheme.com/go/wpjam-basic';
		}
		return $value;
	});
}