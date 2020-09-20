<?php
$taxonomy	= get_current_screen()->taxonomy;

add_filter('wpjam_'.$taxonomy.'_terms_list_action', function($result, $list_action, $term_id, $data){
	if($list_action == 'set_thumbnail'){
		$thumbnail	= $data['thumbnail'] ?? '';
		
		if($thumbnail){
			return update_term_meta($term_id, 'thumbnail', $thumbnail);
		}else{
			return delete_term_meta($term_id, 'thumbnail');
		}
	}elseif($list_action == 'seo'){
		if(!wpjam_has_extend('wpjam-seo')){
			return $result;
		}

		foreach(['seo_title', 'seo_description', 'seo_keywords'] as $meta_key){
			$meta_value	= $data[$meta_key] ?? '';

			if($meta_value){
				update_term_meta($term_id, $meta_key, $meta_value);
			}else{
				delete_term_meta($term_id, $meta_key);
			}
		}

		return true;
	}elseif($list_action == 'posts_per_page'){
		if(!wpjam_has_extend('wpjam-posts-per-page')){
			return $result;
		}

		$posts_per_page	= $data['posts_per_page'] ?? 0;

		if($posts_per_page){
			return update_term_meta($term_id, 'posts_per_page', $posts_per_page);
		}else{
			return delete_term_meta($term_id, 'posts_per_page');
		}
	}

	return $result;
}, 10, 4);

add_filter('wpjam_'.$taxonomy.'_terms_fields', function($fields, $action_key, $term_id, $taxonomy){
	if($action_key == ''){
		if($term_fields	= wpjam_get_term_options($taxonomy)){
			$term_fields	= array_filter($term_fields, function($field){ return !empty($field['show_admin_column']); });
			$fields			= array_merge($fields, $term_fields);
		}
	}elseif($action_key == 'set_thumbnail'){
		if($thumbnail_field	= wpjam_get_term_thumbnail_field($taxonomy)){
			$thumbnail_field['value']	= get_term_meta($term_id, 'thumbnail', true);

			return ['thumbnail'	=> $thumbnail_field];
		}
	}elseif($action_key == 'seo'){
		if(wpjam_has_extend('wpjam-seo')){
			return [
				'seo_title'			=> ['title'=>'SEO 标题',		'type'=>'text',		'value'=>get_term_meta($term_id, 'seo_title', true),	'placeholder'=>'不填则使用文章标题'],
				'seo_description'	=> ['title'=>'SEO 描述', 	'type'=>'textarea',	'value'=>get_term_meta($term_id, 'seo_description', true)],
				'seo_keywords'		=> ['title'=>'SEO 关键字',	'type'=>'text',		'value'=>get_term_meta($term_id, 'seo_keywords', true)]
			];
		}
	}elseif($action_key == 'posts_per_page'){
		if(wpjam_has_extend('wpjam-posts-per-page')){
			return [
				'default'			=> ['title'=>'默认数量',	'type'=>'view',		'value'=>wpjam_get_posts_per_page($taxonomy) ?: get_option('posts_per_page')],
				'posts_per_page'	=> ['title'=>'文章数量',	'type'=>'number',	'value'=>get_term_meta($term_id, 'posts_per_page', true),	'class'=>'']
			];
		}
	}

	return $fields;
}, 10, 4);

add_action('admin_enqueue_scripts', function(){
	$taxonomy	= get_current_screen()->taxonomy;
	$tax_obj	= get_taxonomy($taxonomy);
	$supports	= $tax_obj->supports;
	$levels		= $tax_obj->levels;

	$style		= '.fixed th.column-slug{width:16%;}
	.fixed th.column-description{width:22%;}
	td.column-name img.wp-term-image{float:left; margin:0px 10px 10px 0;}
	.form-field.term-parent-wrap p{display: none;}
	.form-field span.description{color:#666;}
	';

	if($levels == 1){
		$supports	= array_diff($supports, ['parent']);
	}
		
	foreach (['slug', 'description', 'parent'] as $key) { 
		if(!in_array($key, $supports)){
			$style	.= '.form-field.term-'.$key.'-wrap{display: none;}'."\n";
		}
	}

	wp_add_inline_style('wpjam-style', $style);
});

if($thumbnail_field	= wpjam_get_term_thumbnail_field($taxonomy)){
	wpjam_register_term_option('thumbnail', $thumbnail_field);

	add_action('wpjam_'.$taxonomy.'_terms_actions', function($actions, $taxonomy){
		return $actions+['set_thumbnail'=>['title'=>'设置',	'page_title'=>'设置缩略图',	'tb_width'=>'500',	'tb_height'=>'400']];
	}, 10, 2);

	add_filter($taxonomy.'_row_actions', function($row_actions){
		unset($row_actions['set_thumbnail']);		
		return $row_actions;
	});

	add_filter('wpjam_html', function($html){
		if(!wp_doing_ajax() || (wp_doing_ajax() && in_array($_POST['action'], ['inline-save-tax', 'add-tag']))){
			return wpjam_terms_single_row_html_replace($html);
		}elseif(wp_doing_ajax() && $_POST['action'] == 'wpjam-list-table-action'){
			$response	= wpjam_json_decode($html);
			if(isset($response['data'])){
				if(is_array($response['data'])){
					$response['data']	= array_map('wpjam_terms_single_row_html_replace', $response['data']);
				}else{
					$response['data']	= wpjam_terms_single_row_html_replace($response['data']);
				}

				return wpjam_json_encode($response);
			}
		}

		return $html;
	});

	function wpjam_terms_single_row_html_replace($html){
		if(preg_match_all('/<tr id="tag-(\d+)" class=".*?">.*?<\/tr>/is', $html, $matches)){
			$search	= $replace = $matches[0];

			foreach ($matches[1] as $i => $term_id){
				$thumbnail	= wpjam_get_term_thumbnail($term_id, [50,50]);
				$taxonomy	= get_term($term_id)->taxonomy;
				$capability	= get_taxonomy($taxonomy)->cap->edit_terms;

				if(current_user_can($capability)){
					$thumbnail = wpjam_get_list_table_row_action('set_thumbnail',[
						'id'	=> $term_id,
						'title'	=> $thumbnail ?: '<span class="no-thumbnail">暂无图片</span>',
					]);
				}

				$replace[$i]	= str_replace('<a class="row-title"', $thumbnail.'<a class="row-title"', $replace[$i]);
			}

			$html	= str_replace($search, $replace, $html);
		}

		return $html;
	}
}

function wpjam_get_term_thumbnail_field($taxonomy){
	static $thumbnail_field;

	if(isset($thumbnail_field)){
		return $thumbnail_field;
	}

	$thumbnail_field	= [];

	$term_thumbnail_taxonomies	= wpjam_cdn_get_setting('term_thumbnail_taxonomies');

	if($term_thumbnail_taxonomies && in_array($taxonomy, $term_thumbnail_taxonomies)){
		$thumbnail_field	= ['title'=>'缩略图'];

		if(wpjam_cdn_get_setting('term_thumbnail_type') == 'img'){
			$thumbnail_field['type']		= 'img';
			$thumbnail_field['item_type']	= 'url';

			$width	= wpjam_cdn_get_setting('term_thumbnail_width') ?: 200;
			$height	= wpjam_cdn_get_setting('term_thumbnail_height') ?: 200;

			if($width || $height){
				$thumbnail_field['size']		= $width.'x'.$height;
				$thumbnail_field['description']	= '尺寸：'.$width.'x'.$height;
			}
		}else{
			$thumbnail_field['type']	= 'image';
			$thumbnail_field['style']	= 'width:calc(100% - 100px);';
		}
	}

	return $thumbnail_field;	
}