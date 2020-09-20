<?php
// 邮箱验证码相关
if(!isset($_SESSION)){
	session_start();
}
// 简单而直接
if(PHP_VERSION < 7.2){
	if(!is_admin()){
		wp_die('Autumn Pro 主题需要PHP 7.2，你的服务器 PHP 版本为：'.PHP_VERSION.'，请升级到 PHP 7.2。');
		exit;
	}
}elseif(!defined('WPJAM_BASIC_PLUGIN_FILE')){
	if(!is_admin()){
		wp_die('Autumn Pro 主题基于 WPJAM Basic 插件开发，请先<a href="https://wordpress.org/plugins/wpjam-basic/">下载</a>并<a href="'.admin_url('plugins.php').'">激活</a> WPJAM Basic 插件。');
		exit;
	}
}else{
	include TEMPLATEPATH.'/extends/extends.php';

	include TEMPLATEPATH.'/public/utils.php';
	include TEMPLATEPATH.'/public/hooks.php';
	include TEMPLATEPATH.'/public/comment.php';
	include TEMPLATEPATH.'/public/ajax.php';

	include TEMPLATEPATH.'/template-parts/widget/widgets-post.php';
	include TEMPLATEPATH.'/template-parts/widget/widgets-tags.php';

	if(is_admin()){
		include TEMPLATEPATH.'/admin/admin.php';
	}else{
		include TEMPLATEPATH.'/maintenance/maintenance.php';
	}
}