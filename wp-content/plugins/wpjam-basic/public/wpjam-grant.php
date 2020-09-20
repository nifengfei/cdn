<?php
class WPJAM_Grant{
	public static function validate_access_token($token){
		$grants	= self::get_grants();

		if($grants){
			$grants	= array_filter($grants, function($item) use($token){
				return isset($item['token']) && $item['token'] == $token;
			});
		}	

		if(empty($grants)){
			return new WP_Error('invalid_access_token', '非法 Access Token');
		}

		$grant	= current($grants);

		if($grant['token'] != $token || (time()-$grant['time'] > 7200)){
			return new WP_Error('invalid_access_token', '非法 Access Token');
		}

		return true;
	}

	public static function generate_access_token($appid, $secret){
		$grant	= self::get_grant($appid);

		if(is_wp_error($grant)){
			return $grant;
		}

		if(empty($grant['secret']) || $grant['secret'] != md5($secret)){
			return new WP_Error('invalid_secret', '非法密钥');
		}

		$token	= wp_generate_password(64, false, false);
		$time	= time();

		$grant['token']	= $token;
		$grant['time']	= $time;

		self::set_grant($appid, $grant);

		return $token;
	}

	public static function generate_appid(){
		return 'jam'.strtolower(wp_generate_password(15, false, false));
	}

	public static function reset_secret($appid){
		$grant	= self::get_grant($appid);

		if(is_wp_error($grant)){
			return $grant;
		}

		$secret	= strtolower(wp_generate_password(32, false, false));

		$grant['secret']	= md5($secret);

		self::set_grant($appid, $grant);

		return $secret;
	}

	public static function add_appid($appid){
		$items	= self::get_grants();

		if($items){
			if(count($items) >= 3){
				return new WP_Error('too_much_appid', '最多可以设置三个APPID');
			}

			$grant	= self::get_grant($appid);

			if($grant && !is_wp_error($grant)){
				return new WP_Error('appid_exists', 'AppId已存在');
			}
		}

		$items[]	= compact('appid');

		return update_option('wpjam_grant', $items);
	}

	public static function delete_grant($appid){
		$grant	= self::get_grant($appid);

		if(is_wp_error($grant)){
			return $grant;
		}

		$items	= self::get_grants();

		$items	= array_filter($items, function($item) use($appid){
			return $item['appid'] != $appid;
		});

		return update_option('wpjam_grant', array_values($items));
	}

	public static function set_grant($appid, $grant){
		$items	= self::get_grants();
		$update	= false;

		foreach($items as $i => &$item){
			if($item['appid'] == $appid){
				$item	= array_merge($item, $grant);
				$update	= true;
				break;
			}
		}

		if($update){
			return update_option('wpjam_grant', $items);
		}else{
			return true;
		}
	}

	public static function get_grant($appid){
		if(empty($appid)){
			return new WP_Error('invalid_appid', '无效的AppId');
		}

		$items	= self::get_grants();

		if($items){
			$items	= array_filter($items, function($item) use($appid){
				return $item['appid'] == $appid;
			});

			if($items){
				return current($items);
			}
		}

		return new WP_Error('invalid_appid', '无效的AppId');
	}

	public static function get_grants(){
		$items	= get_option('wpjam_grant') ?: [];

		if($items && !wp_is_numeric_array($items)){
			$items	= [$items];

			update_option('wpjam_grant', $items);
		}

		return $items;
	}
}

function wpjam_api_validate_access_token(){
	if(!isset($_GET['access_token']) && is_super_admin()){
		return true;
	}

	$token	= wpjam_get_parameter('access_token', ['required'=>true]);
	$result	= WPJAM_Grant::validate_access_token($token);

	if(is_wp_error($result) && wpjam_is_json_request()){
		wpjam_send_json($result);
	}

	return $result;
}

add_action('wpjam_api_template_redirect', function($json){
	if($json == 'token' || $json == 'token.grant'){
		wpjam_api_validate_quota('token.grant', 1000);

		$appid	= wpjam_get_parameter('appid',	['required' => true]);
		$secret	= wpjam_get_parameter('secret', ['required' => true]);
		$token	= WPJAM_Grant::generate_access_token($appid, $secret);

		if(is_wp_error($token)){
			wpjam_send_json($token);
		}

		wpjam_register_api($json, [
			'access_token'	=> $token,
			'expires_in'	=> 7200
		]);
	}else{
		if($api_setting	= wpjam_get_api($json)){
			if(!empty($api_setting['grant'])){
				wpjam_api_validate_access_token();
			}
		}
	}
});