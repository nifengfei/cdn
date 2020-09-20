<?php
add_filter('wpjam_signup_setting', function(){
	$fields = [];

	if(is_multisite() && is_network_admin()){
		$fields['blog_id']	= ['title'=>'服务号博客ID',	'type'=>'number',	'class'=>'',	'description'=>'微信服务号安装的博客站点ID。'];
	}else{
		$login_actions	= wpjam_get_login_actions(false);

		if(isset($login_actions['weixin'])){
			$fields['weixin']	= ['title'=>'微信公众号',	'type'=>'fieldset',	'fields'=>[
				'weixin_login'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'登录页面支持微信公众号登录'],
				'weixin_bind'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'在后台支持管理员绑定微信公众号']
			]];
		}

		if(isset($login_actions['weapp'])){
			$fields['weapp']	= ['title'=>'微信小程序',	'type'=>'fieldset',	'fields'=>[
				'weapp_login'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>0,	'description'=>'登录页面支持微信小程序登录'],
				'weapp_bind'	=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,	'description'=>'在后台支持管理员绑定微信小程序'],
			]];
		}
	}

	$ajax	= false;

	return compact('fields', 'ajax');
});