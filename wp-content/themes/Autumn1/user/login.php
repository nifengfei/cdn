<?php
if($action == 'weixin-login'){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		$scene	= $_POST['scene'] ?? '';
		$code	= $_POST['code'] ?? '';

		$user 	= wpjam_weixin_qrcode_signup($scene, $code);

		if(is_wp_error($user)){
			$errors	= $user;
		}else{
			wp_safe_redirect( get_option('home').'/user/' );
			die();
		}
	}
}

if(is_weixin()){
	WPJAM_LoginForm::on_login_weixin();
}

if( is_user_logged_in() ){
    wp_safe_redirect( get_option('home').'/user/' );
    die();
}

$login_container = wpjam_theme_get_setting('login_container');
get_header();?>
<style>.site-header,.site-footer{display:none !important}.navbar-sticky,.navbar-sticky_transparent{padding-top:0px}</style>
<div class="login-dahuzi bg-grea-5">
	<div class="container">
		<div class="row login-box">
			<div class="col-lg-5 col-md-12 col-pad-0 bg-img" style="background: rgba(0,0,0,.04) url(<?php $login_bg_img = wpjam_theme_get_setting('login_bg_img') ?: get_template_directory_uri().'/static/images/login_bg_img.jpg'; echo $login_bg_img;?>) top left repeat;background-size: cover">
				<?php if( wpjam_theme_get_setting('login_logo') ){?>
				<a class="none-992" href="<?php echo home_url(); ?>">
					<img src="<?php echo wpjam_theme_get_setting('login_logo');?>" class="logo" alt="<?php echo get_bloginfo('name'); ?>">
				</a>
				<?php }?>
				<h3 class="none-992"><?php echo wpjam_theme_get_setting('login_title');?></h3>
				
				<?php if( in_array($action, ['login']) ){ ?>
					<h3 class="pc-none">登录账号</h3>
					<p class="none-992">
						<?php echo $login_container;?>
					</p>
					<?php if( get_option('users_can_register') ){?>
				        <a href="<?php echo home_url(user_trailingslashit('/user/register')); ?>" class="btn-outline none-992">注册账号</a>
					<?php }?>
				<?php }?>

				<?php if( in_array($action, ['register']) ){ ?>
					<h3 class="pc-none">新用户注册</h3>
					<p class="none-992">
						<?php echo $login_container;?>
					</p>
			        <a href="<?php echo home_url(user_trailingslashit('/user/login')); ?>" class="btn-outline none-992">登录已有账号</a>
			    <?php }?>

				<?php if( in_array($action, ['lostpassword']) ){ ?>
					<h3 class="pc-none">重置密码</h3>
					<p class="none-992">
						<?php echo $login_container;?>
					</p>
			        <a href="<?php echo home_url(user_trailingslashit('/user/login')); ?>" class="btn-outline none-992">返回邮箱登录</a>
			    <?php }?>

				<?php if($action == 'weixin-login'){ ?>
					<h3 class="pc-none">微信扫码登录</h3>
					<p class="none-992">
						<?php echo $login_container;?>
					</p>
			        <a href="<?php echo home_url(user_trailingslashit('/user/login')); ?>" class="btn-outline none-992">返回邮箱登录</a>
			    <?php }?>
				<?php if( get_option('users_can_register') ){?>
					<?php $login_actions = wpjam_get_login_actions('login'); if($login_actions && isset($login_actions['weixin'])){?>
						<div class="block-divider none-992"><span>微信快速登录</span></div>
						<ul class="social-list clearfix none-992">
							<li><a href="<?php echo home_url(user_trailingslashit('/user/weixin-login')); ?>" class="weixin-bg"><i class="iconfont icon-weixin"></i></a></li>
						</ul>
					<?php }?>
				<?php }?>
			</div>

			<?php include get_template_directory().'/user/login/'.$action.'.php'; ?>

		</div>
	</div>
</div>
<?php get_footer();?>