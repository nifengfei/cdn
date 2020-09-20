<?php
// 远程图片处理方式
add_action('init',function(){
	global $wp;

	$wp->add_query_var(CDN_NAME);

	add_rewrite_rule(CDN_NAME.'/([0-9]+)/image/([^/]+)?$', 'index.php?p=$matches[1]&'.CDN_NAME.'=$matches[2]', 'top');
});

// 远程图片加载模板
add_action('template_redirect',	function(){
	if(get_query_var(CDN_NAME)){
		include(WPJAM_BASIC_PLUGIN_DIR.'template/image.php');
		exit;
	}
}, 5);

add_filter('wpjam_content_remote_image', function($img_url){
	$exceptions	= wpjam_cdn_get_setting('exceptions');	// 后台设置不加载的远程图片

	if($exceptions){
		$exceptions	= explode("\n", $exceptions);
		foreach ($exceptions as $exception) {
			if(trim($exception) && strpos($img_url, trim($exception)) !== false ){
				return $img_url;
			}
		}
	}

	$img_type	= strtolower(pathinfo($img_url, PATHINFO_EXTENSION));

	if($img_type != 'gif'){
		$img_type	= ($img_type == 'png')?'png':'jpg';
		$post_id	= $post_id ?: get_the_ID();
		$img_url	= CDN_HOST.'/'.CDN_NAME.'/'.$post_id.'/image/'.md5($img_url).'.'.$img_type;
	}

	return $img_url;
});