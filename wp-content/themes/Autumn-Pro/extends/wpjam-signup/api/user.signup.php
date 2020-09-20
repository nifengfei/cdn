<?php
$scene	= wpjam_get_parameter('scene',	['method'=>'POST',	'required'=>true]);
$code	= wpjam_get_parameter('code',	['method'=>'POST',	'required'=>true]);
$type	= wpjam_get_parameter('type',	['method'=>'POST',	'default'=>'weixin']);

if($type == 'weixin'){
	if(!wpjam_is_weixin_bind_blog()){
		wpjam_send_json([
			'errcode'	=> 'not_weixin_bind_blog',
			'errmsg'	=> '非微信公众号绑定站点'
		]);
	}else{
		$user 	= wpjam_weixin_qrcode_signup($scene, $code, ['users_can_register'=>true]);
	}
}elseif($type == 'weapp'){
	$user	= wpjam_weapp_qrcode_signup($scene, $code);
}

if(is_wp_error($user)){
	wpjam_send_json($user);
}

$user_id	= $user->ID;

$user		= [
	'id'			=> intval($user_id),
	'display_name'	=> $user->display_name,
	'avatar'		=> get_avatar_url($user_id),
];

wpjam_send_json([
	'user'			=> $user,
	'access_token'	=> wpjam_generate_access_token($user_id),
	'expired_in'	=> DAY_IN_SECONDS
]);

