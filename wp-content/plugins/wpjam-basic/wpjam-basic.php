<?php
/*
Plugin Name: WPJAM BASIC
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: WPJAM 常用的函数和接口，屏蔽所有 WordPress 不常用的功能。
Version: 4.4.3
Author: Denis
Author URI: http://blog.wpjam.com/
*/

if (version_compare(PHP_VERSION, '7.2.0') < 0) {
	include plugin_dir_path(__FILE__).'old/wpjam-basic.php';
}else{
	define('WPJAM_BASIC_PLUGIN_URL', plugins_url('', __FILE__));
	define('WPJAM_BASIC_PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('WPJAM_BASIC_PLUGIN_FILE',  __FILE__);

	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-model.php';	// Model 和其操作类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-util.php';		// 通用工具类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-field.php';	// 字段解析类
	include WPJAM_BASIC_PLUGIN_DIR.'includes/class-wpjam-core.php';		// 核心底层类

	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-functions.php';	// 常用函数
	include WPJAM_BASIC_PLUGIN_DIR.'public/wpjam-route.php';		// 路由接口

	do_action('wpjam_loaded');
}