<?php
$scene	= wpjam_get_parameter('scene',	['method'=>'POST']);
$code	= wpjam_get_parameter('code',	['method'=>'POST']);

$openid	= wpjam_verify_weixin_qrcode($scene, $code);

if(is_wp_error($openid)){
	wpjam_send_json($openid);
}

$blacklist	= WEIXIN_User::get_blacklist();
if($blacklist && in_array($openid, $blacklist)){
	wpjam_send_json([
		'errcode'	=>'invalid_openid', 
		'errmsg'	=>'无此微信用户'
	]);
}

$user	= WEIXIN_User::get($openid, $force=true);
$user	= apply_filters('wpjam_qrcode_weixin_user', $user, $openid);

if(!$user){
	wpjam_send_json(['errcode'=>'invalid_openid', 'errmsg'=>'无此微信用户']);
}else{
	wpjam_send_json(compact('user'));
}

