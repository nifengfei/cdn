<?php
if(wpjam_basic_get_setting('disable_block_editor')){
	add_filter('use_block_editor_for_post_type', '__return_false');
}else{
	if(wpjam_basic_get_setting('disable_google_fonts_4_block_editor')){
		// 古腾堡编辑器不加载 Google 字体
		wp_deregister_style('wp-editor-font');
		wp_register_style('wp-editor-font', '');
	}
}

add_action('admin_enqueue_scripts', function(){
	$style	= '';

	global $post;

	$taxonomies	= get_object_taxonomies($post->post_type, 'objects');

	foreach ($taxonomies as $taxonomy => $tax_obj) {
		if(isset($tax_obj->levels) && $tax_obj->levels == 1){
			$style	.= '#new'.$taxonomy.'_parent{display:none;}'."\n";
		}
	}

	if(wpjam_basic_get_setting('disable_trackbacks')){
		$style	.= 'label[for="ping_status"]{display:none !important;}'."\n";
	}

	if($style){
		wp_add_inline_style('wpjam-style', $style);
	}
});

add_filter('post_edit_category_parent_dropdown_args', function($args){
	$tax_obj	= get_taxonomy($args['taxonomy']);
	$levels		= $tax_obj->levels ?? 0;

	if($levels == 1){
		$args['parent']	= -1;
	}elseif($levels > 1){
		$args['depth']	= $levels - 1;
	}

	return $args;
});

// if(wpjam_basic_get_setting('disable_revision')){
//	add_action('wp_print_scripts',function() {
//		wp_deregister_script('autosave');
//	});
// }

add_filter('content_save_pre', function ($content){
	if(wpjam_cdn_get_setting('remote') != 'download'){
		return $content;
	}

	if(!preg_match_all('/<img.*?src=\\\\[\'"](.*?)\\\\[\'"].*?>/i', $content, $matches)){
		return $content;
	}

	$update		= false;
	$search		= $replace	= [];
	$img_urls	= array_unique($matches[1]);

	$img_tags	= $matches[0];

	foreach($img_urls as $i => $img_url){
		if(empty($img_url)){
			continue;
		}

		if(!wpjam_is_remote_image($img_url)){
			continue;
		}

		$exceptions	= wpjam_cdn_get_setting('exceptions');	// 后台设置不加载的远程图片

		if($exceptions){
			$exceptions	= explode("\n", $exceptions);
			foreach ($exceptions as $exception) {
				if(trim($exception) && strpos($img_url, trim($exception)) !== false ){
					continue;
				}
			}
		}

		if(preg_match('/[^\/?]+\.(jpe?g|jpe|gif|png)\b/i', $img_url, $img_match)){
			$file_name	= md5($img_url).'.'.$img_match[1];
		}elseif(preg_match('/data-type=\\\\[\'"](jpe?g|jpe|gif|png)\\\\[\'"]/i', $img_tags[$i], $type_match)){
			$file_name	= md5($img_url).'.'.$type_match[1];
		}else{
			continue;
		}
			
		$file	= wpjam_download_image($img_url, $file_name);

		if(!is_wp_error($file)){
			$search[]	= $img_url;
			$replace[]	= $file['url'];
			$update		= true;
		}
	}

	if($update){
		if(is_multisite()){
			setcookie('wp-saving-post', $_POST['post_ID'].'-saved', time()+DAY_IN_SECONDS, ADMIN_COOKIE_PATH, false, is_ssl());	
		}

		$content	= str_replace($search, $replace, $content);
	}

	return $content;
});

// 跳转回文章编辑页面的当前标签
add_filter('redirect_post_location', function($location, $post_id){
	$referer	= wp_get_referer();
	$fragment	= parse_url($referer, PHP_URL_FRAGMENT);

	if(empty($fragment)){
		return $location;
	}

	if(parse_url($location, PHP_URL_FRAGMENT)){
		return $location;
	}

	return $location.'#'.$fragment;
}, 10, 2);