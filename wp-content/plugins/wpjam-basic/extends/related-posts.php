<?php
/*
Plugin Name: 相关文章
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: 根据文章的标签和分类，自动生成相关文章，并在文章末尾显示。
Version: 1.0
*/
function wpjam_get_related_posts_args(){
	$args	= wpjam_get_option('wpjam-related-posts') ?: [];

	if(empty($args)){
		foreach (['number', 'excerpt',	'post_types', 'class', 'div_id', 'title', 'thumb', 'width', 'height', 'auto'] as $setting_name){
			if($setting_value	= wpjam_basic_get_setting('related_posts_'.$setting_name)){
				$args[$setting_name]	= $setting_value;
			}
		}
	}

	if(!empty($args['thumb'])){
		$args['size']	= [
			'width'		=> !empty($args['width']) ? intval($args['width'])*2 : 0,
			'height'	=> !empty($args['height']) ? intval($args['height'])*2 : 0
		];
	}

	if(!empty($args['post_types'])){
		$args['post_type']	= $args['post_types'];
	}

	return $args;
}

function wpjam_related_posts_shortcode($atts, $content=''){
	extract(shortcode_atts(['tag'=>''], $atts));

	$tags	= $tag ? explode(",", $tag) : '';

	if(empty($tags)){
		return '';
	}
	
	$related_query	= wpjam_query(array( 
		'post_type'		=>'any', 
		'no_found_rows'	=>true,
		'post_status'	=>'publish', 
		'post__not_in'	=>[get_the_ID()],
		'tax_query'		=>[
			[
				'taxonomy'	=> 'post_tag',
				'terms'		=> $tags,
				'operator'	=> 'AND',
				'field'		=> 'name'
			]
		]
	));

	return  wpjam_get_post_list($related_query, ['thumb'=>false,'class'=>'related-posts']);
}
add_shortcode('related', 'wpjam_related_posts_shortcode');

if(!is_admin()){
	add_filter('the_content', function($content){
		$args	= wpjam_get_related_posts_args();

		if(empty($args['auto']) || doing_filter('get_the_excerpt') || !is_singular() || wpjam_get_json() || get_the_ID() != get_queried_object_id()){
			return $content;
		}

		if(!empty($args['post_types']) && !in_array(get_post_type(), $args['post_types'])){
			return $content;
		}
		
		return $content.wpjam_get_related_posts($args);
	}, 11);

	add_filter('wpjam_post_json', function($post_json){
		if(is_singular() && get_the_ID() == get_queried_object_id()){
			$args	= wpjam_get_related_posts_args();
			
			if(empty($args['post_types']) || (in_array(get_post_type(), $args['post_types']))){
				$post_json['related']	= WPJAM_Post::get_related(get_the_ID(), $args);
			}
		}

		return $post_json;
	}, 10, 2);
}


