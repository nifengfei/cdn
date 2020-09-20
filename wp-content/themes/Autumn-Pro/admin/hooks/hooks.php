<?php

wpjam_register_theme_upgrader('http://www.xintheme.com/api?id=57904');

//编辑器增强
add_filter('mce_buttons_3', function ($buttons) {
	$buttons[] = 'del';
	$buttons[] = 'sub';
	$buttons[] = 'sup'; 
	$buttons[] = 'fontselect';
	$buttons[] = 'fontsizeselect';
	$buttons[] = 'cleanup';   
	$buttons[] = 'styleselect';
	$buttons[] = 'wp_page';
	$buttons[] = 'anchor';
	$buttons[] = 'backcolor';
	return $buttons;
});

/*编辑器添加分页按钮*/
add_filter('mce_buttons',function ($mce_buttons) {
	$pos = array_search('wp_more',$mce_buttons,true);
	if ($pos !== false) {
		$tmp_buttons	= array_slice($mce_buttons, 0, $pos+1);
		$tmp_buttons[]	= 'wp_page';
		$mce_buttons	= array_merge($tmp_buttons, array_slice($mce_buttons, $pos+1));
	}
	return $mce_buttons;
});

//字体增加  
add_filter('tiny_mce_before_init', function ($initArray){  
   $initArray['font_formats'] = "微软雅黑='微软雅黑';宋体='宋体';黑体='黑体';仿宋='仿宋';楷体='楷体';隶书='隶书';幼圆='幼圆';";  
   return $initArray;  
});

//去除后台标题中的“—— WordPress”
add_filter('admin_title', function ($admin_title, $title){
	return $title.' &lsaquo; '.get_bloginfo('name');
}, 10, 2);

//移除WPJAM部分功能
add_filter('wpjam_extends_setting', function($wpjam_setting){
	unset($wpjam_setting['fields']['related-posts.php']);
	unset($wpjam_setting['fields']['wpjam-postviews.php']);
	unset($wpjam_setting['fields']['mobile-theme.php']);

	return $wpjam_setting;
}, 99);

add_filter('wpjam_basic_setting', function($wpjam_setting){
	global $plugin_page;

	if($plugin_page == 'wpjam-custom'){
		unset($wpjam_setting['sections']['admin-custom']);
		unset($wpjam_setting['sections']['login-custom']);

		$wpjam_setting['sections']['wpjam-custom']['title']	= '';
	}

	return $wpjam_setting;
}, 11);

add_filter('wpjam_cdn_setting', function($wpjam_setting){
	global $plugin_page;

	if($plugin_page == 'wpjam-thumbnail'){
		unset($wpjam_setting['sections']['thumb']['fields']['default']);
	}

	return $wpjam_setting;
},11);


add_filter('weixin_invite_enable', '__return_false');

add_action('admin_menu',function () {
	remove_submenu_page('users.php', 'wpjam-signup');
});

add_filter('admin_comment_types_dropdown', function($comment_types){
	unset($comment_types['pings']);
	return array_merge($comment_types, ['fav'=>'收藏']);
});