<?php
add_filter('wpjam_user_detail_column', function($nickname, $user_id){
	$openid	= wpjam_get_user_weixin_openid($user_id);

	$openid	= $openid ? '<br />OPENID：'.$openid : '';

	return $nickname.$openid;
}, 10 ,2);