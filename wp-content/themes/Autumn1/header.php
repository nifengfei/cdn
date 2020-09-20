<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<?php 
wp_head();

$favicon		= wpjam_theme_get_setting('favicon') ?: get_template_directory_uri().'/static/images/favicon.ico';
$theme_color	= wpjam_theme_get_setting('theme_color') ?: '#f16b6f';
?>
<link rel="shortcut icon" href="<?php echo $favicon;?>"/>
<style type='text/css'>
html{--accent-color:<?php echo $theme_color;?>}
</style>
</head>
<body id="body" <?php body_class(); ?>>
<div class="site">
	<header class="site-header">
	<div class="container">
		<div class="navbar">
			<div class="branding-within">
				<?php if( $logo = wpjam_theme_get_setting('logo') ) { ?>
				<a class="logo" href="<?php echo home_url(); ?>" rel="home"><img src="<?php echo $logo;?>" alt="<?php echo get_bloginfo('name'); ?>"></a>
				<?php if( $dark_logo = wpjam_theme_get_setting('dark_logo') ) {?>
				<a class="logo dark_logo" href="<?php echo home_url(); ?>" rel="home"><img src="<?php echo $dark_logo;?>" alt="<?php echo get_bloginfo('name'); ?>"></a>
				<?php }?>
				<?php }else{ ?>
				<a class="logo text" href="<?php echo home_url(); ?>" rel="home"><?php echo get_bloginfo('name'); ?></a>
				<?php }?>
			</div>
			<nav class="main-menu hidden-xs hidden-sm hidden-md">
			<ul id="menu-primary" class="nav-list u-plain-list">
				<?php wp_nav_menu(['container'=>false, 'items_wrap'=>'%3$s', 'theme_location'=>'main']); ?>
			</ul>
			</nav>

			<div class="sep sep-right"></div>

			<?php if( wpjam_theme_get_setting('head_dark_switch') ) {?>
			<a href="#" id="dahuzi-dark-switch">
				<i class="iconfont icon-icon_yejianmoshi"></i>
			</a>
			<?php }?>

			<div class="search-open navbar-button">
				<i class="iconfont icon-sousuo"></i>
			</div>
			<?php if( wpjam_theme_get_setting('navbar_user') ) { ?>
			<div class="main-nav">
			<?php if ( is_user_logged_in() ) { ?>
				<?php if(get_option('users_can_register')){?>
					<a class="dahuzi-land cd-signin" href="<?php echo home_url(user_trailingslashit('/user'));?>"><i class="iconfont icon-weidenglu"></i> 用户中心</a>
				<?php }else{?>
					<a class="dahuzi-land cd-signin" href="<?php echo home_url(user_trailingslashit('/wp-admin'));?>"><i class="iconfont icon-weidenglu"></i> 进入后台</a>
				<?php }?>
			<?php }else{?>
				<?php if(get_option('users_can_register')){?>
					<a class="dahuzi-land cd-signin" href="<?php echo home_url(user_trailingslashit('/user/login')); ?>" rel="nofollow"><i class="iconfont icon-weidenglu"></i> 登录</a>
				<?php }else{?>
					<a class="dahuzi-land cd-signin" href="<?php echo home_url(user_trailingslashit('/wp-login.php')); ?>" rel="nofollow"><i class="iconfont icon-weidenglu"></i> 登录</a>
				<?php }?>
			<?php }?>
			</div>
			<?php } ?>

			<div class="main-search">
				<form method="get" class="search-form inline" action="<?php bloginfo('url'); ?>">
					<input type="search" class="search-field inline-field" placeholder="输入关键词进行搜索…" autocomplete="off" value="" name="s" required="true">
					<button type="submit" class="search-submit"><i class="iconfont icon-sousuo"></i></button>
				</form>
				<div class="search-close navbar-button">
					<i class="iconfont icon-guanbi1"></i>
				</div>
			</div>

			<div class="hamburger menu-toggle-wrapper">
				<div class="menu-toggle">
					<span></span>
					<span></span>
					<span></span>
				</div>
			</div>

		</div>
	</div>
	</header>

	<div class="off-canvas">

		<?php if( get_option('users_can_register') ){?>
		<div class="sidebar-header header-cover" style="background-image: url(<?php $login_bg_img = wpjam_theme_get_setting('login_bg_img') ?: get_template_directory_uri().'/static/images/login_bg_img.jpg'; echo $login_bg_img;?>);">
			<div class="sidebar-image">
				<?php if ( is_user_logged_in() ) { ?>
					<img src="<?php echo get_avatar_url(get_current_user_id());?>">
					<a class="dahuzi-land cd-signin" href="<?php echo home_url(user_trailingslashit('/user'));?>"><i class="iconfont icon-weidenglu"></i> 用户中心</a>
				<?php }else{?>
					<img src="<?php echo get_avatar_url(get_current_user_id());?>">
					<a class="dahuzi-land cd-signin" href="<?php echo home_url(user_trailingslashit('/user/login')); ?>" rel="nofollow"><i class="iconfont icon-weidenglu"></i> 登录/注册</a>
				<?php }?>
			</div>
			<?php if ( is_user_logged_in() ) { ?>
				<p class="sidebar-brand"><?php if(get_the_author_meta('description')){ echo the_author_meta( 'description' );}else{ echo '我还没有学会写个人说明！'; }?></p>
			<?php }else{?>
				<p class="sidebar-brand">尊敬的用户，您还未登录，登录之后更精彩！</p>
			<?php }?>
		</div>
		<?php }?>

		<div class="mobile-menu">
		</div>

		<div class="close">
			<i class="iconfont icon-guanbi1"></i>
		</div>
	</div>