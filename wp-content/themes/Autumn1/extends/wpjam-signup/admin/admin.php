<?php
add_filter('wpjam_pages', function ($wpjam_pages){
	$login_actions	= wpjam_get_login_actions('');

	if($login_actions){
		
		$wpjam_pages['users']['subs']['wpjam-signup']	= [
			'menu_title'	=> '登录设置',
			'function'		=> 'option', 
			'option_name'	=> 'wpjam-signup', 
			'page_file'		=> WPJAM_SIGNUP_PLUGIN_DIR.'admin/pages/setting.php'
		];

		$login_actions	= wpjam_get_login_actions('bind');

		if($login_actions){
			$wpjam_pages['users']['subs']['wpjam-bind']	= [
				'menu_title'	=> '账号绑定',			
				'capability'	=> 'read',
				'function'		=> 'tab',
				'page_file'		=> WPJAM_SIGNUP_PLUGIN_DIR.'admin/pages/wpjam-bind.php'
			];
		}

		$login_actions	= wpjam_get_login_actions('invite');

		if($login_actions && isset($login_actions['weixin'])){
			$wpjam_pages['users']['subs']['wpjam-invite']	= [
				'menu_title'	=> '邀请用户',
				'page_file'		=> WPJAM_SIGNUP_PLUGIN_DIR.'admin/pages/wpjam-invite.php'
			];
		}
	}

	return $wpjam_pages;
});


add_filter('wpjam_network_pages', function ($wpjam_pages){
	$wpjam_pages['users']['subs']['wpjam-signup']	= [
		'menu_title'	=> '登录设置',
		'function'		=> 'option', 
		'option_name'	=> 'wpjam-signup', 
		'page_file'		=> WPJAM_SIGNUP_PLUGIN_DIR.'admin/pages/setting.php'
	];

	return $wpjam_pages;
});



add_action('load-user-new.php', function (){
	if(wpjam_get_login_actions('invite') && !current_user_can('manage_sites')){
		wp_redirect(admin_url('users.php?page=wpjam-invite'));
		exit;
	}
});

add_action('admin_menu',function () {
	if(wpjam_get_login_actions('invite') && !current_user_can('manage_sites')){
		remove_submenu_page('users.php', 'user-new.php');
	}
});

add_action('wpjam_users_list_page_file', function(){
	include WPJAM_SIGNUP_PLUGIN_DIR.'admin/hooks/user-list.php';
});

add_action('weixin_activation', function($appid){
	if(!wpjam_is_weixin_bind_blog()){
		return;
	}

	global $wpdb;
	
	$table	= WEIXIN_User::get_table();

	if ($wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'user_id'") != 'user_id') {	
		$wpdb->query("ALTER TABLE $table ADD COLUMN user_id BIGINT(20) NOT NULL");	// 添加 user_id 字段
	}	
});

// add_action('admin_init', 'wpjam_weixin_bind_admin_init');
// function wpjam_weixin_bind_admin_init(){
// 	global $plugin_page;

// 	if($plugin_page != 'weixin-bind'){
// 		$current_user_id	= get_current_user_id();
// 		$weixin_openid		= get_user_meta($current_user_id, WPJAM_Signup::get_weixin_meta_key(), true);
// 		if(empty($weixin_openid)){
// 			wp_redirect(admin_url('users.php?page=weixin-bind'));
// 			exit;
// 		}
// 	}
// }

// add_action( 'admin_notices', function (){
// 	global $plugin_page;

// 	if($plugin_page != 'weixin-bind' ){
// 		if(!wpjam_get_user_openid()){
// 			echo '<div id="admin_notice" class="updated"><p><strong>请<a href="'.admin_url('users.php?page=weixin-bind').'">点击这里绑定您的微信账号</a>，下次登录无需记录账号密码，直接扫码登录！</strong></p></div>';
// 		}
// 	}
// } );

