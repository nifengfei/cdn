<?php
/*
Plugin Name: WPJAM 社交登录
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 使用社交账号登录注册 WordPress，支持一次邀请链接，支持微信服务号 OAuth 2.0 登录和服务号扫码登录。
Author: Denis
Author URI: http://blog.wpjam.com/
Version: 1.0
*/

if(!defined('WPJAM_SIGNUP_PLUGIN_DIR')){
	define('WPJAM_SIGNUP_PLUGIN_DIR',	plugin_dir_path(__FILE__));
	
	function wpjam_signup_loaded(){
		define('WPJAM_SIGNUP_PLUGIN_URL',	plugins_url('', __FILE__));
		define('WPJAM_SIGNUP_PLUGIN_FILE',	__FILE__);

		if(is_multisite() && !defined('WEIXIN_ROBOT_PLUGIN_DIR') && is_dir(WP_PLUGIN_DIR.'/weixin-robot-advanced/')){
			define('WEIXIN_ROBOT_PLUGIN_DIR',	WP_PLUGIN_DIR.'/weixin-robot-advanced/');
		}

		include WPJAM_SIGNUP_PLUGIN_DIR . 'includes/class-signup.php';
		include WPJAM_SIGNUP_PLUGIN_DIR . 'includes/class-invite.php';
		include WPJAM_SIGNUP_PLUGIN_DIR . 'includes/class-login-form.php';

		include WPJAM_SIGNUP_PLUGIN_DIR . 'public/utils.php';
		include WPJAM_SIGNUP_PLUGIN_DIR . 'public/hooks.php';
		include WPJAM_SIGNUP_PLUGIN_DIR . 'public/reply.php';

		if(is_admin()){
			include WPJAM_SIGNUP_PLUGIN_DIR . 'admin/admin.php';
		}
	}

	if(did_action('plugins_loaded')){
		wpjam_signup_loaded();
	}else{
		add_action('plugins_loaded', 'wpjam_signup_loaded');
	}
}

	

