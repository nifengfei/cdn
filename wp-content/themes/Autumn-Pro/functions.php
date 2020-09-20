<?php
// 邮箱验证码相关
if(!isset($_SESSION)){
	session_start();
}
// 简单而直接
if(PHP_VERSION < 7.2){
	if(!is_admin()){
		wp_die('Autumn Pro 主题需要PHP 7.2，你的服务器 PHP 版本为：'.PHP_VERSION.'，请升级到 PHP 7.2。');
		exit;
	}
}elseif(!defined('WPJAM_BASIC_PLUGIN_FILE')){
	if(!is_admin()){
		wp_die('Autumn Pro 主题基于 WPJAM Basic 插件开发，请先<a href="https://wordpress.org/plugins/wpjam-basic/">下载</a>并<a href="'.admin_url('plugins.php').'">激活</a> WPJAM Basic 插件。');
		exit;
	}
}else{
	include TEMPLATEPATH.'/extends/extends.php';

	include TEMPLATEPATH.'/public/utils.php';
	include TEMPLATEPATH.'/public/hooks.php';
	include TEMPLATEPATH.'/public/comment.php';
	include TEMPLATEPATH.'/public/ajax.php';

	include TEMPLATEPATH.'/template-parts/widget/widgets-post.php';
	include TEMPLATEPATH.'/template-parts/widget/widgets-tags.php';

	if(is_admin()){
		include TEMPLATEPATH.'/admin/admin.php';
	}else{
		include TEMPLATEPATH.'/maintenance/maintenance.php';
	}
}

add_action('wp_head', 'zm_admin_referrer');// 前端添加 referrer 标签
add_action('admin_head', 'zm_admin_referrer');// 后台添加 referrer 标签
function zm_admin_referrer(){
echo'<meta name="referrer" content="no-referrer" />';
}

function autoblank($text) {
$return = str_replace('<a', '<a target="_blank"', $text);
return $return;
}
add_filter('the_content', 'autoblank');

add_filter('the_content', 'make_clickable');


function ss_allow_magneted2k_protocol( $protocols ){
    $protocols[] = 'magnet';
    $protocols[] = 'ed2k';
    return $protocols;
}
add_filter( 'kses_allowed_protocols' , 'ss_allow_magneted2k_protocol' );


// sort by modify time
function order_posts_by_mod_date($orderby) 
{   
    if  (is_home() || is_archive() || is_feed()) 
    {     
        $orderby = "post_modified_gmt DESC";   
    }   
    return $orderby; 
} 
add_filter('posts_orderby', 'order_posts_by_mod_date', 999);

//添加老文章提示信息 From wpdaxue.com
function wpdaxue_old_content_message($content) {
	$modified = get_the_modified_time('U'); 
	$current = current_time('timestamp');
	$diffTime = ($current - $modified) / (60*60*24); 
	if($diffTime > 0.0001 && in_category(array(40,167)) ){
		$content = '<div class="old-message">最近一次更新于'.get_the_modified_time('Y年n月j日 l G:i').'，若该剧未及时更新后续资源或者资源失效加QQ群184787642 督促群主补更！</div>'.$content;
	}
	return $content;
}
add_filter( 'the_content', 'wpdaxue_old_content_message' );

//添加老文章提示信息
function old_content_message($content) {
$modified = get_the_modified_time('U');
$current = current_time('timestamp');
$diffTime = ($current - $modified) / (60 * 60 * 24);
if ($diffTime > 2 && in_category(array(1,168)) ) {
$content = '<div class="old-message">最近一次更新于'.get_the_modified_time('Y年n月j日').
'，若该影片资源失效请加QQ群：184787642 督促群主补更！</div>'.$content;
}
return $content;
}
add_filter('the_content', 'old_content_message');


