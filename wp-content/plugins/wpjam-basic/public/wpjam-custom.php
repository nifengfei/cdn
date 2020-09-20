<?php
function wpjam_custom_get_setting($setting_name){
	if(wpjam_get_option('wpjam-custom')){
		return wpjam_get_setting('wpjam-custom', $setting_name);
	}else{
		return wpjam_basic_get_setting($setting_name);
	}
}

if(is_admin()){
	add_action('admin_head', function(){
		remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
		
		add_action('admin_bar_menu', function($wp_admin_bar){
			$admin_logo	= wpjam_custom_get_setting('admin_logo');
			$title 		= $admin_logo ? '<img src="'.wpjam_get_thumbnail($admin_logo, 40, 40).'" style="height:20px; padding:6px 0">' : '<span class="ab-icon"></span>';

			$wp_admin_bar->add_menu([
				'id'    => 'wp-logo',
				'title' => $title,
				'href'  => self_admin_url(),
				'meta'  => ['title'=>get_bloginfo('name')]
			]);
		});

		echo wpjam_custom_get_setting('admin_head');
	});

	if(wpjam_custom_get_setting('admin_footer')){
		add_filter('admin_footer_text', function($text){
			return wpjam_custom_get_setting('admin_footer');
		});
	}
}elseif(is_login()){
	add_filter('login_headerurl', 'home_url');

	add_filter('login_headertext', function(){
		return get_bloginfo('name');
	});

	add_action('login_head', function(){
		echo wpjam_custom_get_setting('login_head'); 
	});

	add_action('login_footer', function(){ 
		echo wpjam_custom_get_setting('login_footer');
	});

	add_filter('login_redirect', function($redirect_to, $request){
		return $request ?: (wpjam_custom_get_setting('login_redirect') ?: $redirect_to);
	}, 10, 2);
}else{
	add_action('wp_head', function(){
		echo wpjam_custom_get_setting('head');
	}, 1);

	add_action('wp_footer', function(){
		echo wpjam_custom_get_setting('footer');

		if(wpjam_basic_get_setting('optimized_by_wpjam')){
			echo '<p id="optimized_by_wpjam_basic">Optimized by <a href="https://blog.wpjam.com/project/wpjam-basic/">WPJAM Basic</a>。</p>';
		}
	}, 99);
}