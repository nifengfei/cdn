<?php
$template_type		= wpjam_shop_get_template_type();
$product_menu_name	= '商品管理';

$shop_show_ui		= wpjam_shop_is_component_blog() ? false : true;

wpjam_register_post_type('product',[
	'label'			=> '商品',
	'labels'		=> [
		'menu_name'	=>$product_menu_name,
		'all_items'	=>'所有商品'
	],
	'public'		=>false,
	'menu_position'	=> 4,
	'menu_icon'		=> 'dashicons-images-alt2',
	'hierarchical'	=> false,
	'rewrite'		=> false,
	'supports'		=> ['title', 'editor'],
	'actions'		=> ['reply','fav'],
	'apply_mu'		=> '',
	'show_ui'		=> $shop_show_ui,
	'weapp_page'	=> 'pages/productDetail/productDetail',
	'pt'			=> 'product',
]);

if($template_type == 'gift_card'){
	wpjam_register_taxonomy('gift_card',[
		'label'			=> '礼品卡',
		'object_type'	=> ['product'],
		'hierarchical'	=> true,
		'rewrite'		=> false,
		'levels'		=> 1,
		'tax'			=> 'gift_card',
		'supports'		=> ['name', 'order']
	]);
}else{
	wpjam_register_taxonomy('product_category',[
		'label'			=> '商品分类',
		'object_type'	=> ['product'],
		'public'		=>false,
		'hierarchical'	=> true,
		'filterable'	=> true,
		'rewrite'		=> false,
		'levels'		=> 2,
		'tax'			=> 'product_category',
		'supports'		=> ['name', 'parent', 'order']
	]);
}

if($template_type != 'gift_card' && $template_type != 'warehouse'){
	$article_supports	= ['title', 'excerpt'];
	if(wpjam_shop_get_article_setting('editor_enable') || !is_admin()){
		$article_supports[]	= 'editor';
	}

	wpjam_register_post_type('article',[
		'label'			=> '文章',
		'labels'		=> [
			'menu_name'	=>'内容电商',
			'all_items'	=>'所有文章'
		],
		'public'		=>false,
		'menu_position'	=> 4,
		'menu_icon'		=> 'dashicons-edit',
		'hierarchical'	=> false,
		'rewrite'		=> false,
		'supports'		=> $article_supports,
		'actions'		=> ['reply', 'like', 'fav'],
		'apply_mu'		=> '',
		'show_ui'		=> $shop_show_ui,
		'pt'			=> 'article',
	]);

	wpjam_register_taxonomy('article_category',[
		'label'			=> '文章分类',
		'object_type'	=> ['article'],
		'hierarchical'	=> true,
		'public'		=> true,
		'rewrite'		=> false,
		'filterable'	=> true,
		'levels'		=> 1,
		'tax'			=> 'article_category',
		'supports'		=> ['name','order']
	]);
}

add_action('init', function(){
	global $shop_show_ui;
	register_post_status('sold_out', [
		'label'						=> '售罄',
		'label_count'				=> _n_noop('售罄 <span class="count">(%s)</span>', '售罄 <span class="count">(%s)</span>'),
		'public'					=> $shop_show_ui,
		'show_in_admin_all_list'	=> $shop_show_ui,
		'show_in_admin_status_list'	=> $shop_show_ui,
		'exclude_from_search'		=> true,
	]);

	register_post_status('unpublished', [
		'label'						=> '已下架',
		'label_count'				=> _n_noop('已下架 <span class="count">(%s)</span>', '已下架 <span class="count">(%s)</span>'),
		'public'					=> $shop_show_ui,
		'show_in_admin_all_list'	=> $shop_show_ui,
		'show_in_admin_status_list'	=> $shop_show_ui,
		'exclude_from_search'		=> true,
	]);
});

add_shortcode('product',  function($atts, $content='') {
	extract(shortcode_atts(['id'=>'', 'type'=>''], $atts));

	$product	= get_post($id);

	if(!$product){
		return $content;
	}

	if($content){
		return '<a href="pages/productDetail/productDetail?id='.$id.'">'.$content.'</a>';
	}else{
		return '
		<a href="pages/productDetail/productDetail?id='.$id.'">
		<div class="product-card">
			<img class="product-card-thumbnail" src="'.WPJAM_Product::get_thumbnail($id).'" />
			<h3 class="product-card-title">'.$product->post_title.'</h3>
			<p class="product-card-except">'.$product->post_excerpt.'</p>
			<div class="product-card-meta">
				<div class="product-card-price">￥'.WPJAM_Product::get_price($id).'</div>
				<div class="product-card-button">去选购</div>
			</div>
		</div>
		</a>
		';
	}
});

add_filter('option_wpjam_post_types', '__return_empty_array');
add_filter('option_wpjam_taxonomies', '__return_empty_array');
