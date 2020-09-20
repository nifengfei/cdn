<?php
add_filter('weixin_builtin_reply', function ($builtin_replies){
	if(wpjam_is_weixin_bind_blog()){
		$builtin_replies['subscribe']	= ['type'=>'full',	'reply'=>'未关注扫码回复',	'function'=>'weixin_subscribe_code_reply'];
		$builtin_replies['scan']		= ['type'=>'full',	'reply'=>'已关注扫码回复',	'function'=>'weixin_scan_code_reply'];
	}
	
	return $builtin_replies;
});


function weixin_subscribe_code_reply($keyword){
	global $weixin_reply;
	$message	= $weixin_reply->get_message();
	$scene		= $message['EventKey'] ?? '';
	$scene		= str_replace('qrscene_','',$scene);

	if($scene && weixin_get_code_reply($scene)){
		if($openid = $weixin_reply->get_weixin_openid()){
			WEIXIN_User::subscribe($openid);
		}
	}else{
		$weixin_reply->subscribe_reply($keyword);
	}
}

function weixin_scan_code_reply($keyword){
	global $weixin_reply;
	$message	= $weixin_reply->get_message();
	$scene		= $message['EventKey'] ?? '';

	if($scene && weixin_get_code_reply($scene)){
	}else{
		$weixin_reply->scan_reply($keyword);
	}
}

function weixin_get_code_reply($scene){
	global $weixin_reply;

	if(is_numeric($scene)){
		return false;
	}

	$openid	= $weixin_reply->get_weixin_openid();
	$code	= wpjam_scan_weixin_qrcode($openid, $scene);

	if(is_wp_error($code)){
		if($code->get_error_code() == 'invalid_scene'){
			return false;
		}else{
			$reply	= $code->get_error_message();
		}
	}elseif(is_numeric($code)){
		$reply	= '你的验证码是 '.$code;
	}else{
		$reply	= '已绑定，请刷新页面！';
	}

	$weixin_reply->textReply($reply);
	return true;
}

// add_action('wpjam_message', function($data){

// 	if($weixin_openid	= get_user_meta($data['receiver'], WEIXIN_BIND_META_KEY, true)){
// 		$send_user = get_userdata($data['sender']);

// 		switch_to_blog( 26 );
// 		include_once(WPJAM_BASIC_PLUGIN_DIR.'include/class-weixin.php');
// 		weixin()->send_custom_message($weixin_openid, $send_user->display_name."给你发送了一条消息：\n\n".$data['content']);

// 		restore_current_blog();
// 	}
// });

// add_filter('insert_user_meta', function($meta, $user, $update){
// 	if($update) return $meta;

// 	unset($meta['first_name']);
// 	unset($meta['last_name']);
// 	unset($meta['description']);
// 	unset($meta['rich_editing']);
// 	unset($meta['syntax_highlighting']);
// 	unset($meta['comment_shortcuts']);
// 	unset($meta['admin_color']);
// 	unset($meta['use_ssl']);
// 	unset($meta['show_admin_bar_front']);

// 	return $meta;

// }, 10, 3);