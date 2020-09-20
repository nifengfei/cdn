<?php
$post_id	= $_GET['post'] ?? 0;

if($post_id){
	if($template_type = get_post_meta($post_id, '_template_type', true)){
		wp_redirect(admin_url('edit.php?post_type=template&page=wpjam-'.$template_type.'&post_id='.$post_id));
	}	
}

add_filter('wpjam_template_post_options', function($post_options){
	global $post;

	$post_options['shortcode_meta_box']	=	[
		'title'		=> '短代码',
		'context'	=> 'side',
		'fields'	=> [
			'shortcode'	=> ['title'=>'',	'type'=>'view',	'value'=>'[template id="'.$post->ID.'"]'],
		]
	];

	return $post_options;
});