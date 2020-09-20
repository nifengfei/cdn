<?php
add_filter('wpjam_posts_tabs', function($tabs){
	$tabs['posts']	= ['title'=>'文章列表',	'function'=>'option', 	'option_name'=>'wpjam-basic'];
	
	if(wpjam_has_extend('related-posts')){
		$tabs['related-posts']	= ['title'=>'相关文章',	'function'=>'option',	'option_name'=>'wpjam-related-posts'];
	}

	if(wpjam_has_extend('wpjam-posts-per-page')){
		$tabs['posts-per-page']	= ['title'=>'文章数量',	'function'=>'option',	'option_name'=>'wpjam-posts-per-page'];

		$post_types = get_post_types(['exclude_from_search'=>false], 'objects');

		if(count($post_types) > 3){
			$tabs['post_types-per-page']	= ['title'=>'文章类型',	'function'=>'option',	'option_name'=>'wpjam-posts-per-page'];
		}
	}

	if(wpjam_has_extend('wpjam-toc')){
		$tabs['toc']	= ['title'=>'文章目录',	'function'=>'option',	'option_name'=>'wpjam-toc'];
	}
	
	return $tabs;
});

add_filter('wpjam_basic_setting', function(){
	$fields	= [
		'post_list_set_thumbnail'	=> ['title'=>'缩略图',	'type'=>'checkbox',	'description'=>'在文章列表页显示和设置文章缩略图。'],
		'post_list_update_views'	=> ['title'=>'浏览数',	'type'=>'checkbox',	'description'=>'在文章列表页显示和修改文章浏览数。'],
		'post_list_author_filter'	=> ['title'=>'作者过滤',	'type'=>'checkbox',	'description'=>'在文章列表页支持通过作者进行过滤。'],
		'post_list_sort_selector'	=> ['title'=>'排序选择',	'type'=>'checkbox',	'description'=>'在文章列表页显示排序下拉选择框。'],
	];

	$summary	= '';

	return compact('fields', 'summary');
});

if(wpjam_has_extend('wpjam-posts-per-page')){
	add_filter('wpjam_posts_per_page_setting', function(){
		global $current_tab;

		if($current_tab == 'posts-per-page'){
			$fields	= [];

			$fields['posts_per_page']	= ['title'=>'全局数量',	'type'=>'number',	'value'=>get_option('posts_per_page'),	'description'=>'博客全局设置的文章列表数量'];
			$fields['posts_per_rss']	= ['title'=>'Feed数量',	'type'=>'number',	'value'=>get_option('posts_per_rss'),	'description'=>'Feed中最近文章列表数量'];

			foreach(['home'=>'首页','author'=>'作者页','search'=>'搜索页','archive'=>'存档页'] as $page_key=>$page_name){
				$fields[$page_key]	= ['title'=>$page_name,	'type'=>'number'];
			}

			$taxonomies = get_taxonomies(['public'=>true,'show_ui'=>true],'objects');

			if(isset($taxonomies['series'])){
				unset($taxonomies['series']);	
			}
			
			if($taxonomies){
				$taxonomies	= wp_list_sort($taxonomies, 'hierarchical', 'DESC', true);
				foreach ($taxonomies as $taxonomy=>$taxonomy_obj) {
					$sub_fields	= [];

					$sub_fields[$taxonomy]	= ['title'=>'',	'type'=>'number'];
					
					if($taxonomy_obj->hierarchical){
						$sub_fields[$taxonomy.'_individual']	= ['title'=>'',	'type'=>'checkbox',	'description'=>'每个'.$taxonomy_obj->label.'可独立设置数量'];
					}

					$fields[$taxonomy.'_set']	= ['title'=>$taxonomy_obj->label,	'type'=>'fieldset',	'fields'=>$sub_fields];
				}
			}
			
			$post_types = get_post_types(['public'=>true, 'has_archive'=>true],'objects');

			if($post_types){
				$sub_fields = [];
				foreach ($post_types as $post_type=>$pt_obj) {
					$sub_fields[$post_type]	= ['title'=>$pt_obj->label,	'type'=>'number'];
				}

				if(count($post_types) == 1){
					$field	= $sub_fields[$post_type];
					$field['title']		.= '存档页';
					$fields[$post_type]	= $field;
				}else{
					$fields['post_type']	= ['title'=>'文章类型存档页',	'type'=>'fieldset',	'fields'=>$sub_fields];
				}
			}

			$summary	= '文章数量扩展可以设置不同页面不同的文章列表数量，也可开启不同的分类不同文章列表数量。<br />空或者0则使用全局设置，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-posts-per-page/" target="_blank">文章数量扩展</a>。';

			return compact('fields', 'summary');
		}else{
			$post_types = get_post_types(['exclude_from_search'=>false],'objects');

			unset($post_types['page']);
			unset($post_types['attachment']);

			$post_type_options	= wp_list_pluck($post_types, 'label');

			$fields	= [];

			foreach(['home'=>'首页','author'=>'作者页','feed'=>'Feed页'] as $page_key=>$page_name){
				$fields[$page_key.'_post_types']	= ['title'=>$page_name,	'type'=>'checkbox',	'value'=>['post'],	'options'=>$post_type_options];
			}

			$summary	= '文章类型扩展可以设置不同页面显示不同文章类型，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-posts-per-page/" target="_blank">文章类型扩展</a>。';

			return compact('fields', 'summary');
		}
	});

	add_filter('pre_update_option_wpjam-posts-per-page', function($value){
		foreach (['posts_per_page', 'posts_per_rss'] as $option_name) {
			if(isset($value[$option_name])){
				if($value[$option_name]){
					update_option($option_name, $value[$option_name]);
				}
				
				unset($value[$option_name]);
			}
		}

		return $value;
	});

	add_filter('option_wpjam-posts-per-page', function($value){
		$value	= $value ?: [];

		$value['posts_per_page']	= get_option('posts_per_page');
		$value['posts_per_rss']		= get_option('posts_per_rss');

		return array_filter($value);
	});
}

if(wpjam_has_extend('related-posts')){
	if(!wpjam_get_option('wpjam-related-posts')){
		$related_value	= [];

		foreach (['number', 'excerpt',	'post_types', 'class', 'div_id', 'title', 'thumb', 'width', 'height', 'auto'] as $setting_name){
			if($setting_value	= wpjam_basic_get_setting('related_posts_'.$setting_name)){
				$related_value[$setting_name]	= $setting_value;
			}

			wpjam_basic_delete_setting('related_posts_'.$setting_name);
		}

		if($related_value){
			update_option('wpjam-related-posts', $related_value);
		}
	}

	add_filter('wpjam_related_posts_setting', function(){
		$post_type_options	= wp_list_pluck(get_post_types(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');

		unset($post_type_options['attachment']);

		$fields	= [
			'title'			=> ['title'=>'标题',		'type'=>'text',		'value'=>'相关文章',	'class'=>'all-options',	'description'=>'相关文章列表标题。'],
			'number'		=> ['title'=>'数量',		'type'=>'number',	'value'=>5,			'class'=>'all-options',	'description'=>'默认为5。'],
			'post_types'	=> ['title'=>'文章类型',	'type'=>'checkbox',	'options'=>$post_type_options,	'description'=>'相关文章列表包含哪些文章类型的文章，默认则为当前文章的类型。'],
			'_excerpt'		=> ['title'=>'摘要',		'type'=>'checkbox',	'name'=>'excerpt',	'description'=>'显示文章摘要。'],
			'thumb_set'		=> ['title'=>'缩略图',	'type'=>'fieldset',	'fields'=>[
					'thumb'		=> ['title'=>'',	'type'=>'checkbox',	'value'=>1,		'description'=>'显示缩略图。'],
					'width'		=> ['title'=>'宽度',	'type'=>'number',	'value'=>100,	'class'=>'small-text',	'show_if'=>['key'=>'thumb', 'value'=>1],	'description'=>'px'],
					'height'	=> ['title'=>'高度',	'type'=>'number',	'value'=>100,	'class'=>'small-text',	'show_if'=>['key'=>'thumb', 'value'=>1],	'description'=>'px']
				]
			],
			'style'			=> ['title'=>'样式',		'type'=>'fieldset',	'fields'=>[
				'div_id'	=> ['title'=>'',	'type'=>'text',	'value'=>'related_posts',	'class'=>'all-options',	'description'=>'外层 div id，不填则外层不添加 div。'],
				'class'		=> ['title'=>'',	'type'=>'text',	'value'=>'',				'class'=>'all-options',	'description'=>'相关文章列表 ul 的 class。'],
			]],
			'auto'			=> ['title'=>'自动',		'type'=>'checkbox',	'value'=>1,	'description'=>'自动附加到文章末尾。'],
		];

		$summary	= '相关文章扩展会在文章详情页生成一个相关文章的列表，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-related-posts/">相关文章扩展</a>。';

		return compact('fields', 'summary');
	});
}
if(wpjam_has_extend('wpjam-toc')){
	if(!wpjam_get_option('wpjam-toc')){
		$toc_value	= [];

		foreach (['toc_depth', 'toc_individual', 'toc_position', 'toc_auto', 'toc_script', 'toc_css', 'toc_copyright'] as $setting_name){
			if($setting_value	= wpjam_basic_get_setting($setting_name)){
				$toc_value[ltrim($setting_name, 'toc_')]	= $setting_value;
			}

			wpjam_basic_delete_setting($setting_name);
		}

		if($toc_value){
			update_option('wpjam-toc', $toc_value);
		}
	}

	add_filter('wpjam_toc_setting', function($sections){
		$fields = [
			'depth'			=> ['title'=>'显示到第几级',	'type'=>'select',	'value'=>6,	'options'=>['1'=>'h1','2'=>'h2','3'=>'h3','4'=>'h4','5'=>'h5','6'=>'h6']],
	    	'individual'	=> ['title'=>'目录单独设置',	'type'=>'checkbox',	'value'=>1,	'description'=>'在每篇文章编辑页面单独设置是否显示文章目录以及显示到第几级。'],
	    	'position'		=> ['title'=>'目录显示位置',	'type'=>'select',	'value'=>'content',	'options'=>['content'=>'显示在文章内容前面','function'=>'调用函数wpjam_get_toc()显示']],
			'auto'			=> ['title'=>'脚本自动插入',	'type'=>'checkbox', 'value'=>1,	'description'=>'自动插入文章目录的 JavaScript 和 CSS 代码，请点击这里获取<a href="https://blog.wpjam.com/m/toc-js-css-code/" target="_blank">文章目录的默认 JS 和 CSS</a>。'],
			'script'		=> ['title'=>'JS代码',		'type'=>'textarea',	'show_if'=>['key'=>'auto', 'value'=>'1'],	'description'=>'如果你没有选择自动插入脚本，可以将下面的 JavaScript 代码复制你主题的 JavaScript 文件中。'],
			'css'			=> ['title'=>'CSS代码',		'type'=>'textarea',	'show_if'=>['key'=>'auto', 'value'=>'1'],	'description'=>'根据你的主题对下面的 CSS 代码做适当的修改。<br />如果你没有选择自动插入脚本，可以将下面的 CSS 代码复制你主题的 CSS 文件中。'],
	    	'copyright'		=> ['title'=>'版权信息',		'type'=>'checkbox', 'value'=>1,	'description'=>'在文章目录下面显示版权信息。']
		];

		$summary	= '文章目录扩展自动根据文章内容里的子标题提取出文章目录，并显示在内容前，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-toc/" target="_blank">文章目录扩展</a>。';

		return compact('fields', 'summary');
	});
}