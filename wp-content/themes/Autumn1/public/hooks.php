<?php

//整个网站  登录后可见
if(wpjam_theme_get_setting('show_only_login')){

	add_action('template_redirect',	function($xintheme_show_only_login) {
		//判断登录
		if( !is_module('user') && !is_user_logged_in() ){
			auth_redirect(); //跳转到登录页面
			exit();
		}
		return $xintheme_show_only_login;
	});

}

//关掉一些WPJAM插件的扩展功能
$wpjam_extends	= get_option('wpjam-extends');
if($wpjam_extends){
	$wpjam_extends_updated	= false;

	//相关文章
	if(!empty($wpjam_extends['related-posts.php'])){
		unset($wpjam_extends['related-posts.php']);
		$wpjam_extends_updated	= true;
	}
	//文章浏览量
	if(!empty($wpjam_extends['wpjam-postviews.php'])){
		unset($wpjam_extends['wpjam-postviews.php']);
		$wpjam_extends_updated	= true;
	}

	if($wpjam_extends_updated){
		update_option('wpjam-extends', $wpjam_extends);
	}
}

add_action('after_setup_theme', function(){
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('post-formats', ['gallery','video','audio']);

	register_nav_menus(['main' =>'主菜单', 'd1' =>'底部菜单①', 'd2' =>'底部菜单②', 'd3' =>'底部菜单③']);

	register_sidebar([
		'name'			=> '全站侧栏',
		'id'			=> 'widget_right',
		'before_widget'	=> '<section class="widget %2$s">', 
		'after_widget'	=> '</section>', 
		'before_title'	=> '<h5 class="widget-title">', 
		'after_title'	=> '</h5>' 
	]);
	register_sidebar([
		'name'			=> '首页侧栏',
		'id'			=> 'widget_home',
		'before_widget'	=> '<section class="widget %2$s">', 
		'after_widget'	=> '</section>', 
		'before_title'	=> '<h5 class="widget-title">', 
		'after_title'	=> '</h5>' 
	]);
	register_sidebar([
		'name'			=> '文章页侧栏',
		'id'			=> 'widget_post',
		'before_widget'	=> '<section class="widget %2$s">', 
		'after_widget'	=> '</section>', 
		'before_title'	=> '<h5 class="widget-title">', 
		'after_title'	=> '</h5>' 
	]);
	register_sidebar([
		'name'			=> '分类/标签/搜索页侧栏',
		'id'			=> 'widget_other',
		'before_widget'	=> '<section class="widget %2$s">', 
		'after_widget'	=> '</section>', 
		'before_title'	=> '<h5 class="widget-title">', 
		'after_title'	=> '</h5>' 
	]);
	register_sidebar([
		'name'			=> '页面侧栏',
		'id'			=> 'widget_page',
		'before_widget'	=> '<section class="widget %2$s">', 
		'after_widget'	=> '</section>', 
		'before_title'	=> '<h5 class="widget-title">', 
		'after_title'	=> '</h5>' 
	]);

});

add_filter('wpjam_template', function($wpjam_template, $module){
	if($module == 'user'){
		return get_template_directory().'/user/index.php';
	}

	return $wpjam_template;
}, 10, 2);

add_filter('wpjam_rewrite_rules', function ($rules){
	$rules['user/posts/page/?([0-9]{1,})$']	= 'index.php?module=user&action=posts&paged=$matches[1]';
	$rules['user/([^/]+)$']					= 'index.php?module=user&action=$matches[1]';
	$rules['user/page/?([0-9]{1,})$']		= 'index.php?module=user&action=posts&paged=$matches[1]';
	$rules['user$']							= 'index.php?module=user&action=posts';

	return $rules;
});

if( wpjam_get_setting('wpjam_theme', 'foot_link') ) {
	add_filter('pre_option_link_manager_enabled', '__return_true');	/*激活友情链接后台*/
}

//载入JS\CSS
add_action('wp_enqueue_scripts', function () {
	if (!is_admin()) {
		
		wp_enqueue_style('style', get_stylesheet_directory_uri().'/static/css/style.css');
		wp_enqueue_style('fonts', get_stylesheet_directory_uri().'/static/fonts/iconfont.css');

		wp_deregister_script('jquery');
		wp_enqueue_script('jquery', "https://cdn.staticfile.org/jquery/3.3.1/jquery.min.js", false, null);
		
		wp_deregister_script('jquery-migrate');
		wp_enqueue_script('jquery-migrate', "https://cdn.staticfile.org/jquery-migrate/3.0.1/jquery-migrate.min.js", false, null);

		wp_enqueue_script('autumn',	get_stylesheet_directory_uri() . '/static/js/autumn.min.js', ['jquery'], '', true);
		wp_localize_script('autumn', 'site_url', ['home_url'=>home_url(),'admin_url'=>admin_url('admin-ajax.php')]);

		if (is_singular() && comments_open() && get_option('thread_comments')){
			wp_enqueue_script( 'comment-reply' );
		}

		if(is_single()){
			wp_enqueue_style('fancybox', 'https://cdn.staticfile.org/fancybox/3.5.7/jquery.fancybox.min.css');
			wp_enqueue_script('fancybox3', 'https://cdn.staticfile.org/fancybox/3.5.7/jquery.fancybox.min.js', ['jquery'], '', true);
		}

		global $wp_query; 
			
		wp_enqueue_script( 'xintheme_ajax', get_stylesheet_directory_uri() . '/static/js/ajax.js', ['jquery'], '', true );
		wpjam_localize_script('xintheme_ajax', 'xintheme', [
			'ajaxurl'		=> admin_url('admin-ajax.php'),
			'query'			=> wpjam_json_encode($wp_query->query),
			'current_page'	=> get_query_var('paged') ?: 1,
			'max_page'		=> $wp_query->max_num_pages ?? 0,
			'paging_type'	=> wpjam_theme_get_setting('paging_xintheme')
		]);
	}	
});

// //删除菜单多余css class
// function wpjam_css_attributes_filter($classes) {
// 	return is_array($classes) ? array_intersect($classes, array('current-menu-item','current-post-ancestor','current-menu-ancestor','current-menu-parent','menu-item-has-children','menu-item')) : '';
// }
// add_filter('nav_menu_css_class',	'wpjam_css_attributes_filter', 100, 1);
// add_filter('nav_menu_item_id',		'wpjam_css_attributes_filter', 100, 1);
// add_filter('page_css_class', 		'wpjam_css_attributes_filter', 100, 1);

add_filter('body_class',function ($classes) {
	//固定导航
	if ( wpjam_get_setting('wpjam_theme', 'navbar_sticky') ){
		$classes[]	= 'navbar-sticky';
	}

	//暗黑风格
	if(wpjam_get_setting('wpjam_theme', 'dark_mode')){
		$classes[]	= 'dark-mode';
	}

	//启用宽版
	if(wpjam_get_setting('wpjam_theme', 'width_1500')){
		$classes[]	= 'width_1500';
	}

	//前端切换暗黑模式 body 添加class
	if(isset($_COOKIE['dahuzi_site_style'])){
		if( $_COOKIE['dahuzi_site_style'] == 'dark' ){
			$classes[] = 'dark-mode';
		}
	}

	//首页图片轮播
	$slide_type = wpjam_theme_get_setting('slide_type');
	if( $slide_type == 'img' ){
		$classes[]	= 'with-hero hero-gallery';
	}

	return $classes;
});

//删除wordpress默认相册样式
add_filter( 'use_default_gallery_style', '__return_false' );

add_filter('wpjam_post_thumbnail_url', function($post_thumbnail_uri, $post){
	if(get_post_meta($post->ID, 'header_img', true)){
		return get_post_meta($post->ID, 'header_img', true);
	}elseif($post_thumbnail_uri){
		return $post_thumbnail_uri;
	}else{
		return wpjam_get_post_first_image($post->post_content);
	}
},10,2);

add_filter('wpjam_default_thumbnail_url',function ($default_thumbnail){
	$default_thumbnails	= wpjam_get_setting('wpjam_theme', 'thumbnails');

	if($default_thumbnails){
		shuffle($default_thumbnails);
		return $default_thumbnails[0];
	}else{
		return $default_thumbnail;
	}
},99);

/* 评论作者链接新窗口打开 */
add_filter('get_comment_author_link', function () {
	$url	= get_comment_author_url();
	$author = get_comment_author();
	if ( empty( $url ) || 'http://' == $url ){
		return $author;
	}else{
		return "<a target='_blank' href='$url' rel='external nofollow' class='url'>$author</a>";
	}
});

//文章自动nofollow
add_filter( 'the_content', function ( $content ) {
	//fancybox3图片添加 data-fancybox
	global $post;
	$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|swf)('|\")(.*?)>(.*?)<\/a>/i";
	$replacement = '<a$1href=$2$3.$4$5 data-fancybox="images" $6>$7</a>';
	$content = preg_replace($pattern, $replacement, $content);
	$content = str_replace(']]>', ']]>', $content);
	return $content;
});

//文章摘要
add_filter('the_excerpt',function($post_excerpt){
	global $post;
	return get_post_excerpt($post, 115);
});

/*
//修复 WordPress 找回密码提示“抱歉，该key似乎无效”
add_filter('retrieve_password_message', function ( $message, $key ) {
	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by('email', trim($_POST['user_login']));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}
	
	$user_login = $user_data->user_login;
	$msg	= __('有人要求重设如下帐号的密码：'). "\r\n\r\n";
	$msg	.= network_site_url() . "\r\n\r\n";
	$msg	.= sprintf(__('用户名：%s'), $user_login) . "\r\n\r\n";
	$msg	.= __('若这不是您本人要求的，请忽略本邮件，一切如常。') . "\r\n\r\n";
	$msg	.= __('要重置您的密码，请打开下面的链接：'). "\r\n\r\n";
	$msg	.= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') ;

	return $msg;
}, null, 2);
*/

add_action('wp_head', function (){
	if(is_singular()) { 
		global $post;
		wpjam_update_post_views($post->ID);
	}
}); 

// 在文章编辑页面的[添加媒体]只显示用户自己上传的文件
function autumn_pro_upload_media( $wp_query_obj ) {
	global $current_user, $pagenow;
	if( !is_a( $current_user, 'WP_User') )
		return;
	if( 'admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments' )
		return;
	if( !current_user_can( 'manage_options' ) && !current_user_can('manage_media_library') )
		$wp_query_obj->set('author', $current_user->ID );
	return;
}
add_action('pre_get_posts','autumn_pro_upload_media');
// 结束

add_action('pre_get_posts',	function($wp_query) {
	global $current_user, $pagenow;

	if($wp_query->is_main_query()){
		if(is_module('user', 'posts')){
			$wp_query->set('ignore_sticky_posts', true);
			$wp_query->set('post_type', 'post');
			$wp_query->set('post_status', 'any');
			$wp_query->set('author',	get_current_user_id());
		}elseif(is_home()){
			$wp_query->set('ignore_sticky_posts',true);
		}elseif(is_search()){
			if(!is_admin()){
				$wp_query->set('post_type', 'post');	//搜索结果排除所有页面
			}
		}elseif(is_tax('group')){
			if(!is_admin()){
				$wp_query->set('post_type', 'topic');	//搜索结果排除所有页面
			}
		}
	}

	return $wp_query;
});

/* 搜索关键词为空 */
add_filter( 'request', function ( $query_variables ) {
	if (isset($_GET['s']) && !is_admin()) {
		if (empty($_GET['s']) || ctype_space($_GET['s'])) {
			wp_redirect( home_url() );
			exit;
		}
	}
	return $query_variables;
});

//删除分类描述P标签 http://www.xintheme.com/wpjiaocheng/49754.html
add_filter('category_description', function($description) {
  $description	= trim($description);
  $description	= wp_strip_all_tags($description);
  return $description;
}); 

//去除自带小工具
add_action("widgets_init", function() {
   //unregister_widget("WP_Widget_Pages");//页面
   //unregister_widget("WP_Widget_Calendar");//文章日程表
   //unregister_widget("WP_Widget_Archives");//文章归档
   //unregister_widget("WP_Widget_Meta");//登入/登出，管理，Feed 和 WordPress 链接
   //unregister_widget("WP_Widget_Search");//搜索
   //unregister_widget("WP_Widget_Categories");//分类目录
   //unregister_widget("WP_Widget_Recent_Posts");//近期文章
   //unregister_widget("WP_Widget_Recent_Comments");//近期评论
   unregister_widget("WP_Widget_RSS");//RSS订阅
   //unregister_widget("WP_Widget_Links");//链接
   //unregister_widget("WP_Widget_Text");//文本
   //unregister_widget("WP_Widget_Tag_Cloud");//标签云
   //unregister_widget("WP_Nav_Menu_Widget");//自定义菜单
   //unregister_widget("WP_Widget_Media_Audio");//音频
   //unregister_widget("WP_Widget_Media_Image");//图片
   //unregister_widget("WP_Widget_Media_Video");//视频
   //unregister_widget("WP_Widget_Media_Gallery");//画廊
});

function wpjam_login_default_settings($value){
	$value	= $value ?: [];
	$value['admin_footer']	= '<style>#tab_thumb>p,#tr_term_thumbnail_set{display:none}</style>Powered by <a href="http://www.xintheme.com" target="_blank">新主题 XinTheme</a> + <a href="https://blog.wpjam.com/" target="_blank">WordPress 果酱</a>';

	return $value;
}
add_filter('option_wpjam-basic', 'wpjam_login_default_settings');
add_filter('default_option_wpjam-basic', 'wpjam_login_default_settings');

//重定向wordpress登录页面
if( !wpjam_theme_get_setting('maintenance_show') && get_option('users_can_register') ){
	add_action('init',function() {
		global $pagenow;
		if( $pagenow == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
			if(!is_user_logged_in()){
				wp_redirect(home_url(user_trailingslashit('/user/login')));
				exit;
			}
		}
	});
}

//非管理员用户禁止访问后台 /wp-admin
if( !wpjam_theme_get_setting('maintenance_show') && wpjam_theme_get_setting('no_wp_admin') ){

	if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		$manage_options = current_user_can( 'manage_options' );
		if(!$manage_options) {
			wp_safe_redirect( home_url(user_trailingslashit('/user')) );
			exit();
		}
	}

}

//直接去掉函数 comment_class() 和 body_class() 中输出的 "comment-author-" 和 "author-"
//避免 WordPress 登录用户名被暴露 
function xintheme_comment_body_class($content){
    $pattern = "/(.*?)([^>]*)author-([^>]*)(.*?)/i";
    $replacement = '$1$4';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
add_filter('comment_class', 'xintheme_comment_body_class');
add_filter('body_class', 'xintheme_comment_body_class');

//添加@评论
add_filter('comment_text', function($comment_text) {
    $comment_ID = get_comment_ID();
    $comment = get_comment($comment_ID);
    if ($comment->comment_parent) {
        $parent_comment = get_comment($comment->comment_parent);
        $comment_text = '<a href="#comment-' . $comment->comment_parent . '"><span class="parent-icon">@' . $parent_comment->comment_author . '</a></span> ' . $comment_text;
    }
    return $comment_text;
});

//延迟加载默认图像
function xintheme_lazysizes(){
	if( wpjam_theme_get_setting('img_lazysizes') ){
		return 'src="'.wpjam_theme_get_setting('img_lazysizes').'"';
	}else{
		return 'src="'.get_template_directory_uri().'/static/images/loading.gif"';
	}
}
