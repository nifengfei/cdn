<?php
function wpjam_weixin_oauth_signup($args=[]){
	return WPJAM_Signup::oauth_signup($args, 'weixin');
}

function wpjam_weixin_qrcode_signup($scene, $code, $args=[]){
	return WPJAM_Signup::qrcode_signup($scene, $code, $args, 'weixin');
}

function wpjam_create_weixin_qrcode($key, $user_id=0){
	return WPJAM_Signup::create_qrcode($key, $user_id, 'weixin');
}

function wpjam_get_weixin_qrcode($scene){
	return WPJAM_Signup::get_qrcode($scene, 'weixin');
}

function wpjam_scan_weixin_qrcode($openid, $scene){
	return WPJAM_Signup::scan_qrcode($openid, $scene, 'weixin');
}

function wpjam_verify_weixin_qrcode($scene, $code){
	return WPJAM_Signup::verify_qrcode($scene, $code, 'weixin');
}

function wpjam_weixin_bind($user_id, $openid){
	return WPJAM_Signup::bind($user_id, $openid, 'weixin');
}

function wpjam_weixin_unbind($user_id, $openid=''){
	return WPJAM_Signup::unbind($user_id, $openid, 'weixin');
}

function wpjam_get_weixin_user($openid){
	return WPJAM_Signup::get_third_user($openid, 'weixin');
}

function wpjam_get_user_weixin_openid($user_id=0){
	$user_id	= $user_id ?: get_current_user_id();

	return WPJAM_Signup::get_user_openid($user_id, 'weixin');
}

function wpjam_get_user_by_weixin_openid($openid){
	return WPJAM_Signup::get_user_by_openid($openid, 'weixin');
}





function wpjam_weapp_qrcode_signup($scene, $code, $args=[]){
	return WPJAM_Signup::qrcode_signup($scene, $code, $args, 'weapp');
}

function wpjam_create_weapp_qrcode($key, $user_id=0){
	return WPJAM_Signup::create_qrcode($key, $user_id, 'weapp');
}

function wpjam_get_weapp_qrcode($scene){
	return WPJAM_Signup::get_qrcode($scene, 'weapp');
}

function wpjam_scan_weapp_qrcode($openid, $scene){
	return WPJAM_Signup::scan_qrcode($openid, $scene, 'weapp');
}

function wpjam_verify_weapp_qrcode($scene, $code){
	return WPJAM_Signup::verify_qrcode($scene, $code, 'weapp');
}

function wpjam_weapp_bind($user_id, $openid){
	return WPJAM_Signup::bind($user_id, $openid, 'weapp');
}

function wpjam_weapp_unbind($user_id, $openid=''){
	return WPJAM_Signup::unbind($user_id, $openid, 'weapp');
}

function wpjam_get_weapp_user($openid){
	return WPJAM_Signup::get_third_user($openid, 'weapp');
}

function wpjam_get_user_weapp_openid($user_id=0){
	$user_id	= $user_id ?: get_current_user_id();

	return WPJAM_Signup::get_user_openid($user_id, 'weapp');
}

function wpjam_get_user_by_weapp_openid($openid){
	return WPJAM_Signup::get_user_by_openid($openid, 'weapp');
}


function wpjam_get_user_openid($user_id=0, $type='weixin'){
	$user_id	= $user_id ?: get_current_user_id();

	return WPJAM_Signup::get_user_openid($user_id, $type);
}

function wpjam_get_user_by_openid($openid, $type='weiixn'){
	return WPJAM_Signup::get_user_by_openid($openid, $type);
}

function wpjam_get_user_phone($user_id=0){
	$user_id	= $user_id ?: get_current_user_id();

	return WPJAM_Signup::get_user_phone($user_id);
}



function wpjam_generate_access_token($user_id){
	return WPJAM_Signup::generate_access_token($user_id);
}

function wpjam_get_user_id_by_access_token($access_token){
	return WPJAM_Signup::get_user_id_by_access_token($access_token);
}


function wpjam_invite_user($role, $args=[]){
	return WPJAM_Invite::create($role, $args);
}

function wpjam_create_invite($role, $args=[]){
	return WPJAM_Invite::create($role, $args);
}

function wpjam_delete_invite($key=''){
	return WPJAM_Invite::delete($key);
}

function wpjam_validate_invite($key=''){
	return WPJAM_Invite::validate($key);
}



function wpjam_signup_get_setting($setting_name){
	return wpjam_get_setting('wpjam-signup', $setting_name);
}

function wpjam_signup_update_setting($setting, $value){
	return wpjam_update_setting('wpjam-signup', $setting, $value);
}


function wpjam_get_login_actions($type='login'){
	$login_actions = [
		'weixin'	=>['title'=>'微信公众号'], 
		'weapp'		=>['title'=>'微信小程序'], 
	];

	if($type == 'login'){
		$weixin_enable	= wpjam_signup_get_setting('weixin_login') ?? true;
		$weapp_enable	= wpjam_signup_get_setting('weapp_login') ?? false;
	}elseif($type == 'bind'){
		$weixin_enable	= wpjam_signup_get_setting('weixin_bind') ?? true;
		$weapp_enable	= wpjam_signup_get_setting('weapp_bind') ?? true;
	}elseif($type == 'invite'){
		$weixin_enable	= apply_filters('weixin_invite_enable', true);
		$weapp_enable	= false;
	}else{
		$weixin_enable	= true;
		$weapp_enable	= true;
	}

	if(!$weixin_enable || !defined('WEIXIN_ROBOT_PLUGIN_DIR')){
		unset($login_actions['weixin']);
	}else{
		if(is_multisite()){
			if(!wpjam_get_weixin_bind_blog_id()){
				unset($login_actions['weixin']);
			}
		}else{
			if(weixin_get_type() < 4){
				unset($login_actions['weixin']);
			}
		}
	}

	if(!$weapp_enable || !defined('WEAPP_PLUGIN_DIR')){
		unset($login_actions['weapp']);
	}else{
		if(!WPJAM_Signup::get_weapp_appid() || !WPJAM_Signup::get_weapp_bind_page()){
			unset($login_actions['weapp']);
		}
	}

	return $login_actions;
}

function wpjam_get_weixin_bind_blog_id(){
	$signup_setting	= get_site_option('wpjam-signup');

	if($signup_setting && !empty($signup_setting['blog_id'])){
		$weixin_bind_blog_id	= $signup_setting['blog_id'];
	}else{
		$weixin_bind_blog_id	= 0;
	}
	
	return apply_filters('weixin_bind_blog_id', $weixin_bind_blog_id);
}

function wpjam_is_weixin_bind_blog(){
	if(is_multisite()){
		return get_current_blog_id() == wpjam_get_weixin_bind_blog_id();	
	}else{
		return true;
	}
}

function wpjam_switch_to_weixin_bind_blog(){
	return is_multisite() ? switch_to_blog(wpjam_get_weixin_bind_blog_id()) : false;
}