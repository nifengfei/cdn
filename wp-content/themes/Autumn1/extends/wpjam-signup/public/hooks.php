<?php
add_action('wpjam_api_template_redirect', function ($json){
	if(in_array($json, ['user.signup', 'user.logout', 'weapp.qrcode.bind', 'weapp.qrcode.code'])){
		include WPJAM_SIGNUP_PLUGIN_DIR . 'api/'.$json.'.php'; 
		exit;
	}

	if(wpjam_is_weixin_bind_blog()){
		if(in_array($json, [ 'weixin.qrcode.create', 'weixin.qrcode.verify'])){
			include WPJAM_SIGNUP_PLUGIN_DIR . 'api/'.$json.'.php'; 
			exit;
		}
	}	
});