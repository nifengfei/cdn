<?php
$post_type	= get_current_screen()->post_type;

add_action('wpjam_'.$post_type.'_posts_actions', function($actions, $post_type){
	if(is_post_type_viewable($post_type)){
		$pt_obj	= get_post_type_object($post_type);
		
		if(wpjam_basic_get_setting('post_list_update_views')){
			$actions['update_views']	= ['title'=>'修改',	'page_title'=>'修改浏览数',	'tb_width'=>'500',	'capability'=>$pt_obj->cap->edit_others_posts];
		}

		$actions['set_thumbnail']	= ['title'=>'设置',	'page_title'=>'设置特色图片',	'tb_width'=>'500',	'tb_height'=>'400'];
	
		if(wpjam_has_extend('duplicate-post')){
			$actions['quick_duplicate']	= ['title'=>'快速复制',	'response'=>'add',	'direct'=>true];
		}

		if(wpjam_has_extend('baidu-zz')){
			$actions['baidu-zz']	= ['title'=>'提交到百度', 'bulk'=>true,	'direct'=>true,	'post_status'=>['publish']];
		}
	}

	return $actions;
}, 9, 2);

add_filter('post_row_actions', function($row_actions, $post){
	foreach (['update_views', 'set_thumbnail'] as $action_key) {
		if(isset($row_actions[$action_key])){
			unset($row_actions[$action_key]);	
		}
	}

	return $row_actions;
}, 10, 2);

add_filter('wpjam_'.$post_type.'_posts_fields', function($fields, $action_key, $post_id, $post_type){
	if($action_key == ''){
		if($post_fields	= wpjam_get_post_fields($post_type)){
			$post_fields	= array_filter($post_fields, function($field){ return !empty($field['show_admin_column']); });
			$fields			= array_merge($fields, $post_fields);
		}

		if($post_type == 'page'){
			$fields['template']	= ['title'=>'模板',	'type'=>'view',	'column_callback'=>'get_page_template_slug'];
		}
		
		if(is_post_type_viewable($post_type)){
			if(wpjam_basic_get_setting('post_list_update_views')){
				$fields['views']	= ['title'=>'浏览',	'type'=>'view',	'column_callback'=>'wpjam_get_admin_post_list_views',	'sortable_column'=>'views'];
			}
		}
	}elseif($action_key == 'update_views'){
		$fields['views']	= ['title'=>'浏览数',	'type'=>'number',	'value'=>wpjam_get_post_views($post_id, false)];
	}elseif($action_key == 'set_thumbnail'){
		$fields['_thumbnail_id']	= ['title'=>'缩略图',	'type'=>'img',		'value'=>get_post_thumbnail_id($post_id),		'size'=>'600x0'];
	}elseif($action_key == 'seo'){
		$fields['seo_title']		= ['title'=>'SEO 标题',		'type'=>'text',		'value'=>get_post_meta($post_id, 'seo_title', true),	'placeholder'=>'不填则使用文章标题'];
		$fields['seo_description']	= ['title'=>'SEO 描述', 		'type'=>'textarea',	'value'=>get_post_meta($post_id, 'seo_description', true)];
		$fields['seo_keywords']		= ['title'=>'SEO 关键字',	'type'=>'text',		'value'=>get_post_meta($post_id, 'seo_keywords', true)];
	}

	return $fields;
}, 10, 4);

add_filter('wpjam_'.$post_type.'_posts_list_action', function($result, $list_action, $post_id, $data){
	if($list_action == 'update_views'){
		if(isset($data['views'])){
			return update_post_meta($post_id, 'views', $data['views']);
		}

		return true;
	}elseif($list_action == 'set_thumbnail'){
		if(!empty($data['_thumbnail_id'])){
			return update_post_meta($post_id, '_thumbnail_id', $data['_thumbnail_id']);
		}else{
			return delete_post_meta($post_id, '_thumbnail_id');
		}
	}elseif($list_action == 'quick_duplicate'){
		if(!wpjam_has_extend('duplicate-post')){
			return $result;
		}

		$post_arr	= get_post($post_id, ARRAY_A);

		unset($post_arr['ID']);
		unset($post_arr['post_date_gmt']);
		unset($post_arr['post_modified_gmt']);
		unset($post_arr['post_name']);

		$post_arr['post_status']	= 'draft';
		$post_arr['post_author']	= get_current_user_id();
		$post_arr['post_date_gmt']	= $post_arr['post_modified_gmt']	= date('Y-m-d H:i:s', time());
		$post_arr['post_date']		= $post_arr['post_modified']		= get_date_from_gmt($post_arr['post_date_gmt']);

		$tax_input	= [];

		$taxonomies	= get_object_taxonomies($post_arr['post_type']);
		foreach($taxonomies as $taxonomy){
			$tax_input[$taxonomy]	= wp_get_object_terms($post_id, $taxonomy, ['fields' => 'ids']);
		}

		$post_arr['tax_input']	= $tax_input;

		$new_post_id	= wp_insert_post($post_arr, true);

		if(is_wp_error($new_post_id)){
			return $new_post_id;
		}

		$meta_keys	= get_post_custom_keys($post_id);

		foreach ($meta_keys as $meta_key) {
			if(in_array($meta_key, ['views', 'likes', 'favs']) || is_protected_meta($meta_key, 'post')){
				continue;
			}

			$meta_values	= get_post_meta($post_id, $meta_key);
			foreach ($meta_values as $meta_value){
				add_post_meta($new_post_id, $meta_key, $meta_value, false);
			}
		}

		return $new_post_id;
	}elseif($list_action == 'baidu-zz'){
		if(!wpjam_has_extend('baidu-zz')){
			return $result;
		}

		$urls	= '';

		if(is_array($post_id)){		
			$post_ids	= $post_id;

			foreach ($post_ids as $post_id) {
				if(get_post($post_id)->post_status == 'publish'){
					if(wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
						wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
						$urls	.= apply_filters('baiduz_zz_post_link', get_permalink($post_id))."\n";	
					}
				}
			}
		}else{
			if(get_post($post_id)->post_status == 'publish'){
				if(wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
					wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
					$urls	.= apply_filters('baiduz_zz_post_link', get_permalink($post_id))."\n";	
				}else{
					return new WP_Error('has_submited', '一小时内已经提交过了');
				}
			}else{
				return new WP_Error('invalid_post_status', '未发布的文章不能同步到百度站长');
			}
		}

		if($urls){
			wpjam_notify_baidu_zz($urls);
		}else{
			return new WP_Error('empty_urls', '没有需要提交的链接');
		}

		return true;
	}elseif($list_action == 'seo'){
		if(!wpjam_has_extend('wpjam-seo')){
			return $result;
		}

		foreach(['seo_title', 'seo_description', 'seo_keywords'] as $meta_key){
			$meta_value	= $data[$meta_key] ?? '';

			if($meta_value){
				update_post_meta($post_id, $meta_key, $meta_value);
			}else{
				delete_post_meta($post_id, $meta_key);
			}
		}
		return true;
	}

	return $result;
}, 10, 4);

add_filter('map_meta_cap', function($caps, $cap, $user_id, $args){
	if($cap == 'edit_post'){
		if(empty($args[0])){
			$post_type	= get_current_screen()->post_type;
			$pt_obj		= get_post_type_object($post_type);

			return !$pt_obj->map_meta_cap ? [$pt_obj->cap->$cap] : [$pt_obj->cap->edit_posts];
		}
	}
	
	return $caps;
}, 10, 4);


function wpjam_get_admin_post_list_views($post_id){
	$post_views	= wpjam_get_post_views($post_id, false);
	$post_type	= get_post($post_id)->post_type;

	if(current_user_can(get_post_type_object($post_type)->cap->edit_others_posts)){
		$post_views	= wpjam_get_list_table_row_action('update_views',[
			'id'	=> $post_id,
			'title'	=> $post_views ?: 0,
		]);	
	}

	return $post_views;
}

function wpjam_get_admin_post_list_thumbnail($post_id, $post_type){
	$post_thumbnail	= wpjam_get_post_thumbnail($post_id, [50,50]);

	if(post_type_supports($post_type, 'thumbnail') && current_user_can('edit_post', $post_id)){
		$post_thumbnail = wpjam_get_list_table_row_action('set_thumbnail',[
			'id'	=> $post_id,
			'title'	=> $post_thumbnail ?: '<span class="no-thumbnail">暂无图片</span>',
		]);
	}

	return $post_thumbnail;
}

add_filter('wpjam_html', function($html){
	$post_type	= get_current_screen()->post_type;

	if(!wp_doing_ajax()){
		if(wpjam_has_extend('quick-excerpt') && post_type_supports($post_type, 'excerpt')){
			$excerpt_inline_edit	= '
			<label>
				<span class="title">摘要</span>
				<span class="input-text-wrap"><textarea cols="22" rows="2" name="the_excerpt"></textarea></span>
			</label>
			';

			$html	= str_replace('<fieldset class="inline-edit-date">', $excerpt_inline_edit.'<fieldset class="inline-edit-date">', $html);
		}
	}

	if(!wp_doing_ajax() || (wp_doing_ajax() && $_POST['action'] == 'inline-save')){
		if(wpjam_basic_get_setting('post_list_set_thumbnail') && (is_post_type_viewable($post_type) || post_type_supports($post_type, 'thumbnail'))){	
			if(preg_match_all('/<tr id="post-(\d+)" class=".*?">.*?<\/tr>/is', $html, $matches)){
				$search	= $replace = $matches[0];

				foreach ($matches[1] as $i => $post_id){
					$replace[$i]	= str_replace('<a class="row-title"', wpjam_get_admin_post_list_thumbnail($post_id, $post_type).'<a class="row-title"', $replace[$i]);
				}

				$html	= str_replace($search, $replace, $html);
			}
		}
	}

	return $html;
});

add_action('add_inline_data', function($post){
	$post_type	= $post->post_type;

	if(wpjam_has_extend('quick-excerpt') && post_type_supports($post_type, 'excerpt')){
		echo '<div class="post_excerpt">' . esc_textarea(trim($post->post_excerpt)) . '</div>';
	}

	if(wpjam_basic_get_setting('post_list_set_thumbnail') && (is_post_type_viewable($post_type) || post_type_supports($post_type, 'thumbnail'))){
		echo '<div class="post_thumbnail">' . wpjam_get_admin_post_list_thumbnail($post->ID, $post_type) . '</div>';
	}
});

add_filter('wp_insert_post_data', function($data, $postarr){
	if(wpjam_has_extend('quick-excerpt') && post_type_supports($data['post_type'], 'excerpt')){
		if(isset($_POST['the_excerpt'])){
			$data['post_excerpt']   = $_POST['the_excerpt'];
		}
	}
		
	return $data;
}, 10, 2);

add_filter('disable_categories_dropdown', '__return_true');

add_action('restrict_manage_posts', function($post_type){
	if($taxonomies	= get_object_taxonomies($post_type, 'objects')){
		foreach($taxonomies as $taxonomy) {

			if(empty($taxonomy->show_admin_column)){
				continue;
			}

			if($taxonomy->name == 'category'){
				if(isset($taxonomy->filterable) && !$taxonomy->filterable){
					continue;
				}

				$taxonomy_key	= 'cat';
			}else{
				if(empty($taxonomy->filterable)){
					continue;
				}

				if($taxonomy->name == 'post_tag'){
					$taxonomy_key	= 'tag_id';
				}else{
					$taxonomy_key	= $taxonomy->name.'_id';
				}
			}

			$selected	= 0;

			if(!empty($_REQUEST[$taxonomy_key])){
				$selected	= intval($_REQUEST[$taxonomy_key]);
			}elseif(!empty($_REQUEST['taxonomy']) && ($_REQUEST['taxonomy'] == $taxonomy->name) && !empty($_REQUEST['term'])){
				if($term	= get_term_by('slug', $_REQUEST['term'], $taxonomy->name)){
					$selected	= $term->term_id;
				}
			}elseif(!empty($taxonomy->query_var) && !empty($_REQUEST[$taxonomy->query_var])){
				if($term	= get_term_by('slug', $_REQUEST[$taxonomy->query_var], $taxonomy->name)){
					$selected	= $term->term_id;
				}
			}

			if($taxonomy->hierarchical){
				wp_dropdown_categories(array(
					'taxonomy'			=> $taxonomy->name,
					'show_option_all'	=> $taxonomy->labels->all_items,
					'show_option_none'	=> '没有设置',
					'hide_if_empty'		=> true,
					'hide_empty'		=> 0,
					'hierarchical'		=> 1,
					'show_count'		=> 0,
					'orderby'			=> 'name',
					'name'				=> $taxonomy_key,
					'selected'			=> $selected
				));
			}else{
				echo wpjam_get_field_html([
					'title'			=> '',
					'key'			=> $taxonomy_key,
					'type'			=> 'text',
					'class'			=> '',
					'value'			=> $selected ?: '',
					'placeholder'	=> '请输入'.$taxonomy->label,
					'data_type'		=> 'taxonomy',
					'taxonomy'		=> $taxonomy->name
				]);
			}
		}
	}

	if(wpjam_basic_get_setting('post_list_author_filter') && post_type_supports($post_type, 'author')){
		wp_dropdown_users([
			'name'						=> 'author',
			'who'						=> 'authors',
			'show_option_all'			=> '所有作者',
			'hide_if_only_one_author'	=> true,
			'selected'					=> wpjam_get_parameter('author', ['method'=>'REQUEST', 'sanitize_callback'=>'intval'])
		]);
	}

	if(wpjam_basic_get_setting('post_list_sort_selector')){

		global $wp_list_table;

		$wp_list_table = $wp_list_table ?: _get_list_table('WP_Posts_List_Table', ['screen'=>$post_type]);
		
		$orderby_options	= [
			''			=> '排序',
			'date'		=> '日期', 
			'modified'	=> '修改时间',
			'ID'		=> get_post_type_object($post_type)->labels->name.'ID',
			'title'		=> '标题', 
		];

		if(post_type_supports($post_type, 'comments')){
			$orderby_options['comment_count']	= '评论';
		}

		if(is_post_type_hierarchical($post_type)){
			// $orderby_options['parent']	= '父级';
		}

		list($columns, $hidden, $sortable_columns, $primary) = $wp_list_table->get_column_info();

		$default_sortable_columns	= $wp_list_table->get_sortable_columns();

		foreach($sortable_columns as $sortable_column => $data){
			if(isset($default_sortable_columns[$sortable_column])){
				continue;
			}

			if(isset($columns[$sortable_column])){
				$orderby_options[$sortable_column]	= $columns[$sortable_column];
			}
		}

		echo wpjam_get_field_html([
			'title'		=>'',
			'key'		=>'orderby',
			'type'		=>'select',
			'value'		=>wpjam_get_parameter('orderby', ['method'=>'REQUEST', 'sanitize_callback'=>'sanitize_key']),
			'options'	=>$orderby_options
		]);

		echo wpjam_get_field_html([
			'title'		=>'',
			'key'		=>'order',
			'type'		=>'select',
			'value'		=>wpjam_get_parameter('order', ['method'=>'REQUEST', 'sanitize_callback'=>'sanitize_key', 'default'=>'DESC']),
			'options'	=>['desc'=>'降序','asc'=>'升序']
		]);
	}
}, 99);

add_filter('posts_clauses', function ($clauses, $wp_query){
	if($wp_query->is_main_query() && $wp_query->is_search()){
		global $wpdb;

		$search_term	= $wp_query->query['s'];

		if(is_numeric($search_term)){
			$clauses['where'] = str_replace('('.$wpdb->posts.'.post_title LIKE', '('.$wpdb->posts.'.ID = '.$search_term.') OR ('.$wpdb->posts.'.post_title LIKE', $clauses['where']);
		}elseif(preg_match("/^(\d+)(,\s*\d+)*\$/", $search_term)){
			$clauses['where'] = str_replace('('.$wpdb->posts.'.post_title LIKE', '('.$wpdb->posts.'.ID in ('.$search_term.')) OR ('.$wpdb->posts.'.post_title LIKE', $clauses['where']);
		}

		if($search_metas = $wp_query->get('search_metas')){
			$clauses['where']	= preg_replace_callback('/\('.$wpdb->posts.'.post_title LIKE (.*?)\) OR/', function($matches) use($search_metas){
				global $wpdb;
				$search_metas	= "'".implode("', '", $search_metas)."'";

				return "EXISTS (SELECT * FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.post_id={$wpdb->posts}.ID AND meta_key IN ({$search_metas}) AND meta_value LIKE ".$matches[1].") OR ".$matches[0];
			}, $clauses['where']);
		}
	}

	return $clauses;
}, 2, 2);

add_action('admin_enqueue_scripts', function(){
	wp_add_inline_style('list-tables', 'td.column-title img.wp-post-image{float:left; margin:0px 10px 10px 0;}
th.manage-column.column-views{width:72px;}
.fixed .column-categories, .fixed .column-tags{width:12%;}');
});