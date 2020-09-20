<?php
add_action('wpjam_user_signuped', ['WPJAM_Invite', 'on_user_signuped'], 10, 2);
class WPJAM_Invite{
	public static function create($role, $args=[]){
		$invites	= self::get_invites();

		if($invites){
			$invites	= array_filter($invites, function($invite){ return $invite['time'] > time(); });
			$invites	= $invites ?: [];

			if(is_multisite() && $invites){
				$blog_invites	 = array_filter($invites, function($invite){ return $invite['blog_id'] == get_current_blog_id(); });

				if(count($blog_invites) > 10){
					return new WP_Error('too_many_invites', '您已经生成了10个邀请链接了，用完再说哈！:-)');
				}
			}
		}else{
			$invites	= [];
		}

		$invite		= [
			'time'	=> time()+8*HOUR_IN_SECONDS,
			'role'	=> $role,
			'args'	=> $args,
		];

		if(is_multisite()){
			$invite['blog_id']	= get_current_blog_id();
		}

		$invite_key	= wp_generate_password(32, false, false);

		$invites[$invite_key]	= $invite;

		self::update_invites($invites);
		
		return $invite_key;
	}

	public static function get($use=true){
		$invite_key	= $_REQUEST['invite_key'] ?? '';

		if($invite_key){
			$invites	= self::get_invites();

			if(!empty($invites) && !empty($invites[$invite_key]) && $invites[$invite_key]['time'] > time()){
				$invite	= $invites[$invite_key];
				
				if($use){
					unset($invites[$invite_key]);
					self::update_invites($invites);
				}

				return $invite;
			}else{
				return new WP_Error('invalid_invite', '无效邀请链接或者邀请链接已过期！');
			}
		}

		return null;
	}

	public static function validate($invite_key=''){
		if($invite_key){
			$invites	= self::get_invites();

			if(!empty($invites) && !empty($invites[$invite_key]) && $invites[$invite_key]['time'] > time()){
				return $invites[$invite_key];
			}else{
				return new WP_Error('invalid_invite', '无效邀请链接或者邀请链接已过期！');
			}
		}

		return null;
	}

	public static function delete($invite_key=''){
		if($invite_key){
			$invites	= self::get_invites();

			if(!empty($invites) && !empty($invites[$invite_key])){
				$invite	= $invites[$invite_key];

				unset($invites[$invite_key]);
				self::update_invites($invites);

				return $invite;
			}
		}

		return null;
	}

	private static function get_invites(){
		$switched	= wpjam_switch_to_weixin_bind_blog();
		$invites	= get_option('wpjam_user_invites');

		if($switched){
			restore_current_blog();
		}

		return $invites;
	}

	private static function update_invites($invites){
		$switched	= wpjam_switch_to_weixin_bind_blog();
		$result		= update_option('wpjam_user_invites', $invites);

		if($switched){
			restore_current_blog();
		}

		return $result;
	}

	public static function on_user_signuped($user, $args){
		$invite_key	= $args['invite'] ?? '';

		if($invite_key){
			$invite	= self::delete($invite_key);

			if(!is_wp_error($user)){
				do_action('wpjam_user_invite', $invite, $user);	
			}
		}
	}
}