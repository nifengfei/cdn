<?php
if(wp_installing()){
	return;
}

add_action('init',function(){
	WPJAM_Route::init();
	WPJAM_Post::init();
	WPJAM_Term::init();
});

add_action('parse_request',				['WPJAM_Route', 'on_parse_request']);
add_action('send_headers',				['WPJAM_Route', 'on_send_headers']);
add_filter('template_include',			['WPJAM_Route', 'filter_template_include'] );
add_filter('pre_term_link',				['WPJAM_Term', 'filter_pre_term_link'], 1, 2);
add_filter('post_type_link', 			['WPJAM_Post', 'filter_post_type_link'], 1, 2);
add_filter('post_password_required',	['WPJAM_Post', 'filter_post_password_required'], 10, 2);
add_filter('posts_clauses',				['WPJAM_Post', 'filter_posts_clauses'], 10, 2);

if(wpjam_is_json_request()){
	remove_filter('the_title', 'convert_chars');

	remove_action('init', 'wp_widgets_init', 1);
	remove_action('init', 'maybe_add_existing_user_to_blog');
	remove_action('init', 'check_theme_switched', 99);

	remove_action('plugins_loaded', '_wp_customize_include');
	
	remove_action('wp_loaded', '_custom_header_background_just_in_time');

	add_filter('determine_current_user',	['WPJAM_Route', 'filter_current_user']);
	add_filter('wp_get_current_commenter',	['WPJAM_Route', 'filter_current_commenter']);
}

if(is_admin()){
	if(!class_exists('WP_List_Table')){
		include ABSPATH.'wp-admin/includes/class-wp-list-table.php';
	}

	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-list-table.php';
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-posts-list-table.php';
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-terms-list-table.php';
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-users-list-table.php';
	
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-plugin-page.php';
	include WPJAM_BASIC_PLUGIN_DIR.'admin/includes/class-wpjam-builtin-page.php';
	
	include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/load.php';
	
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-stats.php';
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-verify.php';
	include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/admin-menus.php';
	include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/hooks.php';
}

include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-crons.php';		// 定时作业
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-grant.php';		// 接口授权
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-notices.php';		// 后台消息
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-messages.php';		// 站内消息
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-cdn.php';			// CDN 处理
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-thumbnail.php';	// 缩略图处理
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-verify-txts.php';	// 验证 TXT
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-hooks.php';		// 基本优化
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-custom.php';		// 样式定制
include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-compat.php';		// 兼容代码 

function wpjam_get_extends(){
	$wpjam_extends	= get_option('wpjam-extends');
	$wpjam_extends	= $wpjam_extends ? array_filter($wpjam_extends) : [];

	if(is_multisite()){
		$wpjam_sitewide_extends	= get_site_option('wpjam-extends');
		$wpjam_sitewide_extends	= $wpjam_sitewide_extends ? array_filter($wpjam_sitewide_extends) : [];
		
		if($wpjam_sitewide_extends){
			$wpjam_extends		= array_merge($wpjam_extends, $wpjam_sitewide_extends);
		}
	}

	return $wpjam_extends;
}

function wpjam_has_extend($extend){
	$extend			= rtrim($extend, '.php').'.php';
	$wpjam_extends	= wpjam_get_extends();

	if($wpjam_extends && isset($wpjam_extends[$extend])){
		return true;
	}else{
		return false;
	}
}

if($wpjam_extends = wpjam_get_extends()){
	foreach (array_keys($wpjam_extends) as $wpjam_extend_file) {
		if(is_file(WPJAM_BASIC_PLUGIN_DIR.'extends/'.$wpjam_extend_file)){
			include WPJAM_BASIC_PLUGIN_DIR.'extends/'.$wpjam_extend_file;
		}
	}
}

add_action('plugins_loaded', function(){
	$template_extend_dir	= get_template_directory().'/extends';

	if(is_dir($template_extend_dir)){
		if($extend_handle = opendir($template_extend_dir)) {   
			while (($extend = readdir($extend_handle)) !== false) {
				if ($extend == '.' || $extend == '..' || is_file($template_extend_dir.'/'.$extend)) {
					continue;
				}
				
				if(is_file($template_extend_dir.'/'.$extend.'/'.$extend.'.php')){
					include $template_extend_dir.'/'.$extend.'/'.$extend.'.php';
				}
			}   
			closedir($extend_handle);   
		}
	}
}, 0);

	
	