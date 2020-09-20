<?php
$taxonomy	= $args['taxonomy'] ?? '';

if(empty($taxonomy)){
	wpjam_send_json(array(
		'errcode'	=> 'empty_taxonomy',
		'errmsg'	=> '自定义分类未设置'
	));
}

$tax_obj	= get_taxonomy($taxonomy);

if(empty($tax_obj)){
	wpjam_send_json(array(
		'errcode'	=> 'invalid_taxonomy',
		'errmsg'	=> '无效的自定义分类'
	));
}

if(isset($args['number'])){
	$number	= $args['number'];
	unset($args['number']);
}else{
	$number	= 0;
}

if(isset($args['mapping'])){
	$mapping	= wp_parse_args($args['mapping']);
	if($mapping && is_array($mapping)){
		foreach ($mapping as $key => $get) {
			if($value = wpjam_get_parameter($get)){
				$args[$key]	= $value;
			}
		}
	}

	unset($args['mapping']);
}

$output		= $args['output'] ?? $taxonomy.'s';

$max_depth	= $args['max_depth'] ?? ($tax_obj->levels ?? -1);
if($terms = wpjam_get_terms($args, $max_depth)){
	if($number){
		$paged	= $args['paged'] ?? 1;
		$offset	= $number * ($paged-1);

		$response['current_page']	= (int)$paged;
		$response['total_pages']	= ceil(count($terms)/$number);
		$terms = array_slice($terms, $offset, $number);
	}
	$response[$output]	= array_values($terms);
}else{
	$response[$output]	= array();
}

if($taxonomy == 'category'){
	$taxonomy_title	= '分类';
}elseif($taxonomy == 'post_tag'){
	$taxonomy_title	= '标签';
}else{
	$taxonomy_title	= get_taxonomy($taxonomy)->label;
}

if(empty($response['page_title'])){
	$response['page_title']	= $taxonomy_title;
}

if(empty($response['share_title'])){
	$response['share_title']	= $taxonomy_title;
}