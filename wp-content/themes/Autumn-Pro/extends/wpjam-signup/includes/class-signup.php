<?php
class WPJAM_Signup{
	public static function oauth_signup($args=[], $type='weixin'){
		if($type == 'weixin'){
			if(!wpjam_is_weixin_bind_blog()){
				$redirect_to	= $_REQUEST['redirect_to'] ?? admin_url();
				
				$switched		= wpjam_switch_to_weixin_bind_blog();

				$redirect_to	= home_url('/wp-login.php?action=weixin&redirect_to='.urlencode($redirect_to));
				
				$invite_key		= $_REQUEST['invite_key'] ?? '';

				if($invite_key){
					$redirect_to	.= '&invite_key='.$invite_key;
				}

				if($switched){
					restore_current_blog();
				}
				
				wp_redirect($redirect_to, 301);
				exit;
			}else{
				$openid	= WEIXIN_User::get_current_openid();

				if(is_wp_error($openid)){
					WEIXIN_User::oauth_request('snsapi_userinfo');
				}else{
					return self::signup($openid, $args, $type);
				}
			}
		}
	}

	public static function qrcode_signup($scene, $code, $args=[], $type='weixin'){
		$openid	= self::verify_qrcode($scene, $code, $type);

		if(is_wp_error($openid)){
			return $openid;
		}

		return self::signup($openid, $args, $type);
	}

	private static function signup($openid, $args, $type='weixin'){
		$user	= self::get_user_by_openid($openid, $type);

		if(is_wp_error($user)){
			return $user;
		}else{
			if($user){
				$user	= self::login($user, $args);
			}else{
				$user	= self::register($openid, $args, $type);
			}

			do_action('wpjam_user_signuped', $user, $args);	

			return $user;
		}
	}

	private static function login($user, $args){

		wp_set_auth_cookie($user->ID, true);
		wp_set_current_user($user->ID);

		$blog_id	= $args['blog_id'] ?? '';
		$role		= $args['role'] ?? '';

		if($role){
			if(is_multisite() && $blog_id){
				if(!is_user_member_of_blog($user->ID, $blog_id)){
					add_user_to_blog($blog_id, $user->ID, $role);
				}else{

					$switched	= switch_to_blog($blog_id);	
					$user		= get_userdata($user->ID);		// 不同博客的用户角色不同

					if($switched){
						restore_current_blog();
					}

					if(!in_array($role, $user->roles)){
						return new WP_Error('user_registered', '你已有权限，如果需要更改权限，请联系管理员直接修改。');
					}
				}
			}else{
				if(!in_array($role, $user->roles)){
					return new WP_Error('user_registered', '你已有权限，如果需要更改权限，请联系管理员直接修改。');
				}
			}
		}
			
		return $user;
	}

	public static function register($openid, $args=[], $type='weixin'){
		$register_lock	= wp_cache_get($openid, 'register_lock');
		
		if($register_lock !== false){
			return new WP_Error('username_registering', '该用户名正在注册中，请稍后再试！');
		}

		$result	= wp_cache_add($openid, 1, 'register_lock', 15);
		if($result === false){
			return new WP_Error('username_registering', '该用户名正在注册中1，请稍后再试！');
		}

		$third_user	= self::get_third_user($openid, $type);

		$users_can_register	= $args['users_can_register'] ?? self::users_can_register();

		if($users_can_register){
			$domain		= parse_url(home_url(), PHP_URL_HOST);

			$user_name	= preg_replace( '/\s+/', '', sanitize_user($openid, true));
			$user_login	= wp_slash($user_name);
			$user_email	= wp_slash($openid.'@'.$domain);
			$user_pass	= wp_generate_password( 12, false );

			$role		= $args['role'] ?? get_option('default_role');

			$userdata	= compact('user_login', 'user_email', 'user_pass','role');

			if(!empty($third_user['nickname'])){
				$userdata['nickname']	= $userdata['display_name']	= $third_user['nickname'];
			}

			if(is_multisite()){	
				$blog_id	= $args['blog_id'] ?? 0;

				$switched	= $blog_id ? switch_to_blog($blog_id) : false;
				$user_id	= wp_insert_user($userdata);

				if($switched){
					restore_current_blog();
				}

			}else{
				$user_id	= wp_insert_user($userdata);
			}

			if(is_wp_error($user_id)){
				return $user_id;
			}

			$result		= self::bind($user_id, $openid, $type);

			wp_set_auth_cookie($user_id, true);
			wp_set_current_user($user_id);

			return get_userdata($user_id);
		}else{
			$headimg	= '';
			if($type == 'weixin' && isset($third_user['headimgurl'])){
				$headimg = '<img src="'.str_replace('/64', '/0', $third_user['headimgurl']).'" style="width:24px; margin:0 10px -5px 0;" />'.$third_user['nickname'].'，';
			}

			return new WP_Error('register_disabled', $headimg.'系统不支持直接注册，请联系管理员！');
		}
	}

	public static function bind($user_id, $openid, $type='weixin'){
		$meta_key	= self::get_meta_key($type);
		$third_user	= self::get_third_user($openid, $type);

		if($third_user){

			if($third_user['user_id']	!= $user_id){
				if(get_userdata($third_user['user_id'])){
					return new WP_Error($type.'_already_binded', '该微信已经绑定其他账号了。');
				}else{
					self::update_third_user($openid, compact('user_id'), $type);
				}
			}

			$user_openid = get_user_meta($user_id, $meta_key, true);

			if(empty($user_openid) || $openid != $user_openid){
				update_user_meta($user_id, $meta_key, $openid);
			}

			if($type == 'weixin'){
				$avatar_field	= 'headimgurl';
			}elseif($type == 'weapp'){
				$avatar_field	= 'avatarurl';
			}

			if(!empty($third_user[$avatar_field])){
				$avatarurl = get_user_meta($user_id, 'avatarurl', true);

				if(empty($avatarurl) || $third_user[$avatar_field] != $avatarurl){
					update_user_meta($user_id, 'avatarurl', $third_user[$avatar_field]);
				}			
			}

			if(!empty($third_user['nickname'])){
				$user	= get_userdata($user_id);
				
				if($user->nickname != $third_user['nickname']){
					$userdata	= ['ID'=>$user_id];
					$userdata['nickname']	= $userdata['display_name']	= $third_user['nickname'];

					add_filter('insert_user_meta', function($meta){
						return ['nickname'=>$meta['nickname']];
					});

					wp_update_user($userdata);
				}
			}
		}	

		return get_userdata($user_id);	
	}

	public static function unbind($user_id, $openid='', $type='weixin'){
		$openid		= $openid ?: self::get_user_openid($user_id, $type);
		$meta_key	= self::get_meta_key($type);

		if($type == 'weixin'){
			wp_cache_delete($openid, 'weixin_wp_users');
		}

		self::update_third_user($openid, ['user_id'=>''], $type);

		delete_user_meta($user_id, $meta_key);
		delete_user_meta($user_id, 'avatarurl');	

		return true;
	}

	public static function get_weixin_openid_by_unionid($unionid){
		$switched	= wpjam_switch_to_weixin_bind_blog();

		include_once WEIXIN_ROBOT_PLUGIN_DIR . 'public/utils.php';
		include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin.php';
		include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/trait-weixin.php';
		include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin-user.php';
		
		$openid = WEIXIN_User::Query()->where('unionid', $unionid)->get_var('openid');

		if($switched){
			restore_current_blog();
		}

		return $openid;
	}

	public static function get_third_user($openid, $type='weixin'){
		if($type == 'weixin'){
			$switched	= wpjam_switch_to_weixin_bind_blog();

			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'public/utils.php';
			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin.php';
			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/trait-weixin.php';
			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin-user.php';

			$blacklist	= WEIXIN_User::get_blacklist();
			if($blacklist && in_array($openid, $blacklist)){
				return new WP_Error('invalid_openid', '无此微信用户');
			}

			$weixin_user	= WEIXIN_User::get($openid, true);
			
			if($switched){
				restore_current_blog();
			}
			
			return $weixin_user;
		}else{
			$weapp_appid	= self::get_weapp_appid();
			WEAPP_User::set_appid($weapp_appid);

			return WEAPP_User::get($openid);
		}
	}

	public static function update_third_user($openid, $data, $type='weixin'){
		if($type == 'weixin'){
			$switched	= wpjam_switch_to_weixin_bind_blog();

			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'public/utils.php';
			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin.php';
			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/trait-weixin.php';
			include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin-user.php';

			$blacklist	= WEIXIN_User::get_blacklist();
			if($blacklist && in_array($openid, $blacklist)){
				return new WP_Error('invalid_openid', '无此微信用户');
			}

			$result	= WEIXIN_User::update($openid, $data);
			
			if($switched){
				restore_current_blog();
			}
			
			return $result;
		}else{
			$weapp_appid	= self::get_weapp_appid();
			WEAPP_User::set_appid($weapp_appid);

			return WEAPP_User::update($openid, $data);
		}
	}

	public static function create_qrcode($key, $user_id=0, $type='weixin'){
		if($type == 'weixin'){
			$switched	= wpjam_switch_to_weixin_bind_blog();

			$qrcode	= wp_cache_get($key, $type.'_qrcode');

			if($qrcode === false){

				include_once WEIXIN_ROBOT_PLUGIN_DIR . 'includes/class-weixin.php';
				include_once WEIXIN_ROBOT_PLUGIN_DIR . 'public/utils.php';

				$scene	= self::generate_scene('weixin');
				$qrcode = weixin()->create_qrcode('QR_STR_SCENE', $scene, 1200);

				if(!is_wp_error($qrcode)){
					$qrcode['key']		= $key;
					$qrcode['scene']	= $scene;

					if($user_id){
						$qrcode['user_id']	= $user_id;
					}else{
						$qrcode['code']		= rand(1000,9999);
					}
					
					wp_cache_set( $key, $qrcode, $type.'_qrcode', 1200 );
					wp_cache_set( $scene, $qrcode, $type.'_scene', 1200 );
				}
			}

			if($switched){
				restore_current_blog();
			}
		}elseif($type == 'weapp'){
			$qrcode	= wp_cache_get($key, $type.'_qrcode');

			if($qrcode === false){

				$weapp_appid	= self::get_weapp_appid();

				if($weapp_appid){
					$scene	= self::generate_scene('weapp');
					if($user_id){
						$scene_str	= 'bind='.$scene;
					}else{
						$scene_str	= 'signup='.$scene;
					}
					
					$weapp_page		= self::get_weapp_bind_page();
					$qrcode_url		= weapp_create_qrcode(['page'=>$weapp_page, 'scene'=>$scene_str, 'type'=>'unlimit', 'width'=>430], 'url', $weapp_appid);
					$code 			= rand(1000,9999);

					if(is_wp_error($qrcode_url)){
						return $qrcode_url;
					}

					$qrcode	= compact('key', 'code', 'scene', 'qrcode_url', 'user_id');

					wp_cache_set( $key, $qrcode, $type.'_qrcode', 1200 );
					wp_cache_set( $scene, $qrcode, $type.'_scene', 1200 );
				}else{
					$qrcode	= [];
				}
			}
		}

		return $qrcode;	
	}

	public static function generate_scene($type='weixin'){
		if($type == 'weixin'){
			$length	= 24;
		}elseif($type == 'weapp'){
			$length	= 11;
		}

		return wp_generate_password($length,false,false).microtime(true)*10000;
	}

	public static function get_weapp_bind_page(){
		return apply_filters('weapp_bind_page', '');
	}

	public static function get_qrcode($scene, $type='weixin'){
		if(empty($scene)){
			return new WP_Error('invalid_scene', '场景值不能为空');
		}

		if($type == 'weixin'){
			$switched	= wpjam_switch_to_weixin_bind_blog();

			$qrcode		= wp_cache_get($scene, $type.'_scene');
			
			if($switched){
				restore_current_blog();
			}
		}elseif($type == 'weapp'){
			$qrcode = wp_cache_get($scene, $type.'_scene');
		}

		if($qrcode === false){
			return new WP_Error('invalid_scene', '请刷新获取二维码，再来验证！');
		}

		return $qrcode;
	}

	public static function verify_qrcode($scene, $code, $type='weixin'){
		if(empty($code)){
			return new WP_Error('invalid_code', '验证码不能为空');
		}

		$qrcode	= self::get_qrcode($scene, $type);

		if(is_wp_error($qrcode)){
			return $qrcode;
		}

		if($code && $code != $qrcode['code']){
			return new WP_Error('invalid_code', '验证码错误！');
		}

		if(empty($qrcode['openid'])){
			return new WP_Error('invalid_code', '二维码未被扫描！');
		}

		wp_cache_delete( $scene, $type.'_scene' );
		
		return $qrcode['openid'];
	}

	public static function scan_qrcode($openid, $scene, $type='weixin'){
		$qrcode = self::get_qrcode($scene, $type);
		
		if(is_wp_error($qrcode)){
			return $qrcode;
		}

		if(!empty($qrcode['openid']) && $qrcode['openid'] != $openid){
			return new WP_Error('qrcode_scaned', '已有用户扫描该二维码！');
		}else{
			$key	= $qrcode['key'];
			wp_cache_delete( $key, $type.'_qrcode' );

			if(!empty($qrcode['user_id'])){
				wp_cache_delete( $scene, $type.'_scene' );
				return self::bind($qrcode['user_id'], $openid, $type);
			}else{
				$qrcode['openid'] = $openid;
				wp_cache_set( $scene, $qrcode, $type.'_scene', 1200 );

				return $qrcode['code'];
			}
		}
	}

	public static function get_weapp_appid(){
		if($weapp_appid	= weapp_get_appid()){
			return $weapp_appid;
		}

		$weapp_settings	= weapp_get_settings();

		if($weapp_settings){
			return $weapp_settings[0]['appid'];
		}else{
			return '';
		}
	}

	public static function get_user_openid($user_id, $type='weixin'){
		return get_user_meta($user_id, self::get_meta_key($type), true);
	}

	public static function get_user_phone($user_id){
		return get_user_meta($user_id, 'phone', true);
	}

	public static function get_user_by_openid($openid, $type='weixin'){
		$third_user	= self::get_third_user($openid, $type);

		if(is_wp_error($third_user)){
			return $third_user;
		}elseif(empty($third_user)){
			return null;
		}

		if($third_user){
			$user_id	= $third_user['user_id'] ?? 0;	
		}else{
			$user_id	= 0;
			// return new WP_Error('invalid_openid', '无此微信用户');
		}

		if($type == 'weixin'){
			if($user_id){
				$result		= self::bind($user_id, $openid, $type);

				if(is_wp_error($result)){
					return $result;
				}
			}else{
				$user_id	= wp_cache_get($openid, 'weixin_wp_users');

				if($user_id === false){
					$meta_key	= self::get_meta_key($type);
					$users		= get_users(['meta_key'=>$meta_key, 'meta_value'=>$openid, 'blog_id'=>0]);

					if($users){
						$user_id	= $users[0]->ID;

						$result		= self::bind($user_id, $openid, $type);
						
						if(is_wp_error($result)){
							return $result;
						}
					}else{
						$user_id = username_exists($openid);	// 最后尝试

						if($user_id){
							$has_openid = self::get_user_openid($user_id);

							if($has_openid && $has_openid != $openid){
								// 妈的不知道怎么回事了
							}else{
								$result		= self::bind($user_id, $openid, $type);

								if(is_wp_error($result)){
									return $result;
								}
							}	
						}else{
							wp_cache_set($openid, 0, 'weixin_wp_users', MINUTE_IN_SECONDS);
						}
					}
				}
			}

			if($user_id){
				return get_userdata($user_id);
			}else{
				return null;
			}
		}elseif($type == 'weapp'){
			if($user_id){
				return get_userdata($user_id);
			}else{
				$weixin_openid = $third_user['weixin_openid'] ?? '';

				if(empty($weixin_openid)){
					// 小程序需要和公众号配合，通过 union 找到 公众号的 openid ，再通过公众号的 openid 找到 WP 的 user_id
					$unionid	= $third_user['unionid'];

					if(empty($unionid)){
						return new WP_Error('empty_unionid','请先授权！');
					}

					$weixin_openid = self::get_weixin_openid_by_unionid($unionid);

					if(empty($weixin_openid)) {
					    return new WP_Error('user_not_exists','用户不存在');
					}

					self::update_third_user($openid, ['weixin_openid'=>$weixin_openid], $type);
				}
				
				$user	= self::get_user_by_openid($weixin_openid, 'weixin');

				if($user && !is_wp_error($user)){
					self::update_third_user($openid, ['user_id'=>$user->ID], $type);
				}

				return $user;
			}
		}		
	}

	public static function get_meta_key($type='weixin'){
		if($type == 'weixin'){
			return	apply_filters('weixin_bind_meta_key', 'weixin_openid');
		}elseif($type == 'weapp'){
			if(is_multisite()){
				$meta_key	= 'wp_'.get_current_blog_id().'_weapp_openid';
			}else{
				$meta_key	= 'weapp_openid';
			}

			return	apply_filters('weapp_bind_meta_key', $meta_key);
		}
	}

	public static function users_can_register(){
		if(is_multisite()){
			return false;
		}else{
			return get_option('users_can_register');
		}
	}

	public static function generate_access_token($user_id){
		$switched	= wpjam_switch_to_weixin_bind_blog();

		$access_token	= wp_cache_get($user_id, 'wpjam_user_access_token');

		if($access_token !== false){
			wp_cache_delete($user_id,		'wpjam_user_access_token');
			wp_cache_delete($access_token,	'wpjam_user_access_token');
		}

		$access_token	= md5(uniqid($user_id.time()));
		$expired_in		= DAY_IN_SECONDS;

		wp_cache_set($access_token, $user_id, 'wpjam_user_access_token', $expired_in);
		wp_cache_set($user_id, $access_token, 'wpjam_user_access_token', $expired_in);

		if($switched){
			restore_current_blog();
		}

		return $access_token;
	}

	public static function get_user_id_by_access_token($access_token){
		$switched	= wpjam_switch_to_weixin_bind_blog();

		$user_id	= wp_cache_get($access_token, 'wpjam_user_access_token');
		
		if($user_id === false){
			return new WP_Error('illegal_access_token', 'Token 非法或已过期！');
		}

		if($switched){
			restore_current_blog();
		}

		return $user_id;
	}

	public static function logout(){
		$user_id	= get_current_user_id();
		
		$access_token	= wp_cache_get($user_id, 'wpjam_user_access_token');

		if($access_token !== false){
			wp_cache_delete($user_id,		'wpjam_user_access_token');
			wp_cache_delete($access_token,	'wpjam_user_access_token');
		}

		wp_logout();
	}
}