<?php
add_action('login_init', 		['WPJAM_LoginForm', 'on_login_init']);
add_action('login_head', 		['WPJAM_LoginForm', 'on_login_head']);
add_action('login_footer', 		['WPJAM_LoginForm', 'on_login_footer'], 999);
add_action('login_form_login',	['WPJAM_LoginForm', 'on_login_action']);
add_action('login_form_weixin',	['WPJAM_LoginForm', 'on_login_weixin']);
add_action('login_form_weapp',	['WPJAM_LoginForm', 'on_login_weapp']);

add_filter('shake_error_codes',	['WPJAM_LoginForm', 'filter_shake_error_codes']);

class WPJAM_LoginForm{
	private static $login_action	= 'login';

	public static function on_login_init(){
		global $interim_login;
		$interim_login = isset( $_REQUEST['interim-login'] );

		if(empty($_COOKIE[TEST_COOKIE])){
			$_COOKIE[TEST_COOKIE]	= 'WP Cookie check';
		}
	}

	public static function on_login_head(){
		if(wp_is_mobile() && !is_weixin()){
			return;
		}

		wp_enqueue_script('jquery');

		?>
		<style type="text/css">p#nav{display:none;} </style>
		<?php
	}

	public static function on_login_action(){
		$login_actions	= wpjam_get_login_actions();

		if(isset($login_actions['weixin'])){
			self::on_login_weixin();
		}elseif(isset($login_actions['weapp'])){
			self::on_login_weapp();
		}
	}

	public static function on_login_weapp(){
		$login_actions	= wpjam_get_login_actions();

		if(!isset($login_actions['weapp'])){
			return;
		}

		if(isset($login_actions['weixin'])){
			if(wp_is_mobile()){
				return;
			}
		}else{
			if(wp_is_mobile() && !is_weixin()){
				return;
			}
		}

		self::$login_action	= 'weapp';

		if($_SERVER['REQUEST_METHOD'] == 'POST'){

			if(empty($_GET['action']) || $_GET['action']!='weapp'){
				return;
			}

			$scene	= $_POST['scene'] ?? '';
			$code	= $_POST['code'] ?? '';

			$user 	= wpjam_weixin_qrcode_signup($scene, $code);

			if(is_wp_error($user)){
				$errors	= $user;
			}else{
				self::login();
			}
		}

		$errors = new WP_Error();

		self::login_form($errors, 'weapp');
	}

	public static function on_login_weixin(){

		$login_actions	= wpjam_get_login_actions();

		if(!isset($login_actions['weixin'])){
			return;
		}
		
		$invite_key	= $_REQUEST['invite_key'] ?? '';

		if(empty($invite_key)){
			if(isset($_REQUEST['action'])){
				if($_REQUEST['action'] == 'login'){
					return;
				}
			}
		}

		if(wp_is_mobile() && !is_weixin()){
			return;
		}

		$redirect_to	= $_REQUEST['redirect_to'] ?? '';
		$redirect_to	= $redirect_to ?: admin_url();

		if(is_weixin() && !wpjam_is_weixin_bind_blog()){
			$switched		= wpjam_switch_to_weixin_bind_blog();

			$redirect_to	= home_url('/wp-login.php?action=weixin&redirect_to='.urlencode($redirect_to));

			if($invite_key){
				$redirect_to	.= '&invite_key='.$invite_key;
			}

			if($switched){
				restore_current_blog();
			}
			
			wp_redirect($redirect_to, 301);
			exit;
		}

		$errors	= new WP_Error();

		$invite	= wpjam_validate_invite($invite_key);

		if(is_wp_error($invite)){
			$errors = $invite;
		}else{
			$args	= [];

			if($invite){
				$args['invite']				= $invite_key;
				$args['role']				= $invite['role'];
				$args['blog_id']			= $invite['blog_id'];
				$args['users_can_register']	= true;
			}

			if(is_weixin()){
				$user	= wpjam_weixin_oauth_signup($args);

				if(is_wp_error($user)){
					wp_die($user, '错误', ['response'=>200]);
				}else{
					wp_redirect($redirect_to, 301);
					exit;
				}	
			}else{
				self::$login_action	= 'weixin';

				if($_SERVER['REQUEST_METHOD'] == 'POST'){

					if(empty($_GET['action']) || $_GET['action']!='weixin'){
						return;
					}

					$scene	= $_POST['scene'] ?? '';
					$code	= $_POST['code'] ?? '';

					$user 	= wpjam_weixin_qrcode_signup($scene, $code, $args);

					if(is_wp_error($user)){
						$errors	= $user;
					}else{
						self::login();
					}
				}
			}
		}
		
		self::login_form($errors, 'weixin');
	}

	public static function login(){
		global $interim_login;
						
		if ( $interim_login ) {
			$message       = '<p class="message">' . __( 'You have logged in successfully.' ) . '</p>';
			$interim_login = 'success';
			login_header( '', $message );
			?>
			</div>
			<?php do_action( 'login_footer' ); ?>
			</body></html>
			<?php
		}else{
			$redirect_to	= $_REQUEST['redirect_to'] ?? '';
			$redirect_to	= $redirect_to ?: admin_url();
			wp_redirect($redirect_to);
		}

		exit;
	}

	public static function login_form($errors, $type='weixin'){
		if(isset($_COOKIE[$type.'_key'])){
			$key	= $_COOKIE[$type.'_key'];
		}else{
			$key	= wp_generate_password(32, false, false);
			wpjam_set_cookie($type.'_key', $key, time()+30);
		}

		if($type == 'weixin'){
			$wpjam_qrcode	= wpjam_create_weixin_qrcode($key);	
		}else{
			$wpjam_qrcode	= wpjam_create_weapp_qrcode($key);
		}

		if(is_wp_error($wpjam_qrcode)){
			wp_redirect(home_url('/wp-login.php?action=login'));
			exit;
			wp_die($wpjam_qrcode->get_error_message().$wpjam_qrcode->get_error_code().'二维码创建失败，请刷新重试！');
		}

		if($type == 'weixin'){
			$qrcode_url	= 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$wpjam_qrcode['ticket'];

			login_header('微信登录','',$errors);
		}else{
			$qrcode_url	= $wpjam_qrcode['qrcode_url'];

			login_header('小程序登录','',$errors);
		}

		if(self::$login_action == $type){
			global $interim_login;

			$login_url	= site_url( 'wp-login.php?action=weixin', 'login_post' );

			if($interim_login){
				$login_url	= add_query_arg(['interim-login'=>1], $login_url);
			}

			$redirect_to	= $_REQUEST['redirect_to'] ?? '';
			$invite_key		= $_REQUEST['invite_key'] ?? '';
			$scene			= $wpjam_qrcode['scene'];

			?>
			<form name="loginform" id="loginform" action="<?php echo esc_url($login_url); ?>" method="post">
				<p>
					<label for="code">微信扫码，一键登录<br />
					<img src="<?php echo $qrcode_url; ?>" width="272" /></label>
				</p>
				<p>
					<label for="code">验证码<br />
					<input type="input" name="code" id="code" class="input" value="" size="20" /></label>
				</p>

				<?php do_action( 'login_form' );?>

				<input type="hidden" name="scene" value="<?php echo $scene; ?>" />
				<input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />
				<input type="hidden" name="invite_key" value="<?php echo $invite_key; ?>" />
				
				<p class="submit">
					<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Log In'); ?>" />
				</p>
			</form>
			<?php
		}

		login_footer('code');
		exit;
	}

	public static function on_login_footer(){
		if(wp_is_mobile() && !is_weixin()){
			return;
		}

		global $interim_login;

		if(empty($_REQUEST['invite_key'])){
			$action	= $_REQUEST['action'] ?? 'weixin';

			$redirect_to	= $_REQUEST['redirect_to'] ?? '';
			$redirect_to	= $redirect_to ?: '';

			$login_actions	= wpjam_get_login_actions();

			if(isset($login_actions['weixin'])){
				$login_actions['weixin']['login_text']	= '使用微信登录';
			}

			$login_actions['login']	= ['title'=>'账号密码'];

			unset($login_actions[self::$login_action]);

			$login_text	= '<p style="line-height:30px; float:left;">';

			foreach ($login_actions as $login_key => $login_action) {
				$login_url	= site_url( 'wp-login.php?action='.$login_key, 'login_post' );
				if($interim_login){
					$login_url	= add_query_arg(['interim-login'=>1], $login_url);
				}

				if($redirect_to){
					$login_url	= add_query_arg(['redirect_to'=>urlencode($redirect_to)], $login_url);
				}

				$login_action_text	= $login_action['login_text'] ?? '使用'.$login_action['title'].'登录';
				$login_text	.= '<a style="text-decoration: none;" href="' . esc_url($login_url) . '">'.$login_action_text.'</a><br />';
			}

			$login_text	.= '<p style="line-height:30px; float:left;">';

			if(self::$login_action == 'login'){
				$login_text	= '<p style="clear:both;"></p>'.$login_text;
			}

			?>
			<script type="text/javascript">
			jQuery(function($){
				$('p.submit').after('<?php echo $login_text; ?>');
			});
			</script>
			<?php
		}	
	}

	public static function filter_shake_error_codes($shake_error_codes){
		$shake_error_codes[]	= 'invalid_code';
		$shake_error_codes[]	= 'invalid_openid';
		$shake_error_codes[]	= 'invalid_scene';
		$shake_error_codes[]	= 'weixin_already_binded';
		$shake_error_codes[]	= 'weapp_already_binded';
		// $shake_error_codes[]	= 'invalid_invite';
		return $shake_error_codes;
	}
}