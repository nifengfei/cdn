<?php
global $plugin_page;

add_filter('wpjam_content_template_setting', function(){
	if(wp_doing_ajax()){
		$referer_origin	= parse_url(wpjam_get_referer());
		$referer_args	= wp_parse_args($referer_origin['query']);
		$post_type		= $referer_args['post_type'] ?? 'post';

	}else{
		$post_type		= $_GET['post_type'] ?? 'post';	
	}
	

	$fields	= [
		$post_type.'_top'		=> ['title'=>'顶部设置',	'type'=>'textarea'],
		$post_type.'_bottom'	=> ['title'=>'底部设置',	'type'=>'textarea'],
	];

	return compact('fields');
});