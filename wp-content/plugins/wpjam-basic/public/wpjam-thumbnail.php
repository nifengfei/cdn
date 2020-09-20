<?php
function wpjam_thumbnail_get_setting($setting_name){
	return wpjam_get_setting('wpjam-cdn', $setting_name);
}

// 1. $img_url 
// 2. $img_url, array('width'=>100, 'height'=>100)	// 这个为最标准版本
// 3. $img_url, 100x100
// 4. $img_url, 100
// 5. $img_url, array(100,100)
// 6. $img_url, array(100,100), $crop=1, $retina=1
// 7. $img_url, 100, 100, $crop=1, $retina=1
function wpjam_get_thumbnail(){
	$args_num	= func_num_args();
	$args		= func_get_args();

	$img_url	= $args[0];

	if(strpos($img_url, '?') === false){
		$img_url	= str_replace(['%3A','%2F'], [':','/'], urlencode(urldecode($img_url)));	// 中文名
	}

	if($args_num == 1){	
		// 1. $img_url 简单替换一下 CDN 域名

		$thumb_args = [];
	}elseif($args_num == 2){		
		// 2. $img_url, ['width'=>100, 'height'=>100]	// 这个为最标准版本
		// 3. $img_url, [100,100]
		// 4. $img_url, 100x100
		// 5. $img_url, 100		

		$thumb_args = wpjam_parse_size($args[1]);
	}else{
		if(is_numeric($args[1])){
			// 6. $img_url, 100, 100, $crop=1, $retina=1

			$width	= $args[1] ?? 0;
			$height	= $args[2] ?? 0;
			$crop	= $args[3] ?? 1;
			// $retina	= $args[4] ?? 1;
		}else{
			// 7. $img_url, array(100,100), $crop=1, $retina=1

			$size	= wpjam_parse_size($args[1]);
			$width	= $size['width'];
			$height	= $size['height'];
			$crop	= $args[2]??1;
			// $retina	= $args[3]??1;
		}

		// $width		= intval($width)*$retina;
		// $height		= intval($height)*$retina;

		$thumb_args = compact('width','height','crop');
	}

	$thumb_args	= apply_filters('wpjam_thumbnail_args', $thumb_args);

	return apply_filters('wpjam_thumbnail', $img_url, $thumb_args);
}

function wpjam_parse_size($size, $retina=1){
	global $content_width;	

	$_wp_additional_image_sizes = wp_get_additional_image_sizes();

	if(is_array($size)){
		if(wpjam_is_assoc_array($size)){
			$size['width']	= $size['width'] ?? 0;
			$size['height']	= $size['height'] ?? 0;
			$size['width']	*= $retina;
			$size['height']	*= $retina;
			$size['crop']	= !empty($size['width']) && !empty($size['height']);
			return $size;
		}else{
			$width	= intval($size[0]??0);
			$height	= intval($size[1]??0);
			$crop	= $width && $height;
		}
	}else{
		if(strpos($size, 'x')){
			$size	= explode('x', $size);
			$width	= intval($size[0]);
			$height	= intval($size[1]);
			$crop	= $width && $height;
		}elseif(is_numeric($size)){
			$width	= $size;
			$height	= 0;
			$crop	= false;
		}elseif($size == 'thumb' || $size == 'thumbnail'){
			$width	= intval(get_option('thumbnail_size_w'));
			$height = intval(get_option('thumbnail_size_h'));
			$crop	= get_option('thumbnail_crop');

			if(!$width && !$height){
				$width	= 128;
				$height	= 96;
			}

		}elseif($size == 'medium'){

			$width	= intval(get_option('medium_size_w')) ?: 300;
			$height = intval(get_option('medium_size_h')) ?: 300;
			$crop	= get_option('medium_crop');

		}elseif( $size == 'medium_large' ) {

			$width	= intval(get_option('medium_large_size_w'));
			$height	= intval(get_option('medium_large_size_h'));
			$crop	= get_option('medium_large_crop');

			if(intval($content_width) > 0){
				$width	= min(intval($content_width), $width);
			}

		}elseif($size == 'large'){

			$width	= intval(get_option('large_size_w')) ?: 1024;
			$height	= intval(get_option('large_size_h')) ?: 1024;
			$crop	= get_option('large_crop');

			if (intval($content_width) > 0) {
				$width	= min(intval($content_width), $width);
			}
		}elseif(isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$size])){
			$width	= intval($_wp_additional_image_sizes[$size]['width']);
			$height	= intval($_wp_additional_image_sizes[$size]['height']);
			$crop	= $_wp_additional_image_sizes[$size]['crop'];

			if(intval($content_width) > 0){
				$width	= min(intval($content_width), $width);
			}
		}else{
			$width	= 0;
			$height	= 0;
			$crop	= 0;
		}
	}

	$width	= $width * $retina;
	$height	= $height * $retina;

	return compact('width','height', 'crop');
}

// 默认缩略图
function wpjam_get_default_thumbnail_url($size='full', $crop=1){
	$thumbnail_url	= apply_filters('wpjam_default_thumbnail_url', wpjam_thumbnail_get_setting('default'));
		
	return $thumbnail_url ? wpjam_get_thumbnail($thumbnail_url, $size, $crop) : '';
}

// 文章缩略图
add_filter('wpjam_post_thumbnail_url', function ($thumbnail_url, $post){
	$thumbnail_url		= $thumbnail_url ?: wpjam_get_default_thumbnail_url();
	$thumbnail_orders	= wpjam_thumbnail_get_setting('post_thumbnail_orders') ?: [];

	if(empty($thumbnail_orders)){
		return $thumbnail_url;
	}

	foreach ($thumbnail_orders as $thumbnail_order) {
		if($thumbnail_order['type'] == 'first'){
			if($post_first_image = wpjam_get_post_first_image_url($post)){
				return $post_first_image;
			}
		}elseif($thumbnail_order['type'] == 'post_meta'){
			if($post_meta 	= $thumbnail_order['post_meta']){
				if($post_meta_url = get_post_meta($post->ID, $post_meta, true)){
					return $post_meta_url;
				}
			}
		}elseif($thumbnail_order['type'] == 'term'){
			if(!wpjam_thumbnail_get_setting('term_thumbnail_type')){
				continue;
			}

			$taxonomy	= $thumbnail_order['taxonomy'];

			if(empty($taxonomy)){
				continue;
			}

			$thumbnail_taxonomies	= $thumbnail_taxonomies ?? wpjam_thumbnail_get_setting('term_thumbnail_taxonomies');

			if(empty($thumbnail_taxonomies) || !in_array($taxonomy, $thumbnail_taxonomies)){
				continue;
			}

			$post_taxonomies	= $post_taxonomies ?? get_post_taxonomies($post);

			if(empty($post_taxonomies) || !in_array($taxonomy, $post_taxonomies)){
				continue;
			}
			
			if($terms = get_the_terms($post, $taxonomy)){
				foreach ($terms as $term) {
					if($term_thumbnail = wpjam_get_term_thumbnail_url($term)){
						return $term_thumbnail;
					}
				}
			}
		}
	}

	return $thumbnail_url;
}, 1, 2);

// 分类缩略图
add_filter('wpjam_term_thumbnail_url', function($thumbnail_url, $term){
	if(!wpjam_thumbnail_get_setting('term_thumbnail_type')){
		return $thumbnail_url;
	}

	$thumbnail_taxonomies	= wpjam_thumbnail_get_setting('term_thumbnail_taxonomies');

	if(empty($thumbnail_taxonomies) || !in_array($term->taxonomy, $thumbnail_taxonomies)){
		return $thumbnail_url;
	}

	return get_term_meta($term->term_id, 'thumbnail', true);
}, 1, 2);