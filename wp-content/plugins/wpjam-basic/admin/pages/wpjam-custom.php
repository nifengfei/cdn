<?php
if(!wpjam_get_option('wpjam-custom')){
	$custom_value	= [];

	foreach (['admin_logo', 'admin_head', 'admin_footer', 'head', 'footer', 'login_head', 'login_footer', 'login_redirect'] as $setting_name){
		if($setting_value	= wpjam_basic_get_setting($setting_name)){
			$custom_value[$setting_name]	= $setting_value;
		}

		wpjam_basic_delete_setting($setting_name);
	}

	if($custom_value){
		update_option('wpjam-custom', $custom_value);
	}
}

add_filter('wpjam_custom_setting', function(){
	$admin_fields = [
		'admin_logo'	=> ['title'=>'后台左上角 Logo',		'type'=>'img',	'item_type'=>'url',	'description'=>'建议大小：20x20。'],
		'admin_head'	=> ['title'=>'后台 Head 代码 ',		'type'=>'textarea',	'class'=>''],
		'admin_footer'	=> ['title'=>'后台 Footer 代码',		'type'=>'textarea',	'class'=>'']
	];

	$custom_fields = [
		'head'			=> ['title'=>'前台 Head 代码',		'type'=>'textarea',	'class'=>''],
		'footer'		=> ['title'=>'前台 Footer 代码',		'type'=>'textarea',	'class'=>''],
	];

	$login_fields = [
		// 'login_logo'			=> ['title'=>'登录界面 Logo',		'type'=>'img',		'description'=>'建议大小：宽度不超过600px，高度不超过160px。'),
		'login_head'	=> ['title'=>'登录界面 Head 代码',	'type'=>'textarea',	'class'=>''],
		'login_footer'	=> ['title'=>'登录界面 Footer 代码',	'type'=>'textarea',	'class'=>''],
		'login_redirect'=> ['title'=>'登录之后跳转的页面',		'type'=>'text'],
	];

	$sections	= [ 
		'wpjam-custom'	=> ['title'=>'前台定制',	'fields'=>$custom_fields],
		'admin-custom'	=> ['title'=>'后台定制',	'fields'=>$admin_fields],
		'login-custom'	=> ['title'=>'登录界面', 	'fields'=>$login_fields]
	];

	return compact('sections');
});