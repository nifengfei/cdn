<?php
class WPJAM_Builtin_Page{
	public  static function load($current_screen){
		global $wpjam_list_table;

		$screen_base	= $current_screen->base;

		do_action('wpjam_builtin_page_load', $screen_base, $current_screen);

		if($screen_base == 'term' || $screen_base == 'edit-tags') {
			$taxonomy	= $current_screen->taxonomy ?? '';

			if($taxonomy && get_taxonomy($taxonomy)){
				add_filter('term_updated_messages',			['WPJAM_Builtin_Page', 'filter_term_updated_messages']);
				add_filter('taxonomy_parent_dropdown_args',	['WPJAM_Builtin_Page', 'filter_taxonomy_parent_dropdown_args'], 10, 3);

				add_action($taxonomy.'_add_form_fields', 	['WPJAM_Builtin_Page', 'on_add_form_fields']);
				add_action($taxonomy.'_edit_form_fields', 	['WPJAM_Builtin_Page', 'on_edit_form_fields']);

				add_filter('pre_insert_term', 				['WPJAM_Builtin_Page', 'filter_pre_insert_term'], 10, 2);
				add_action('created_term', 					['WPJAM_Builtin_Page', 'on_created_term'], 10, 3);
				add_action('edited_term', 					['WPJAM_Builtin_Page', 'on_edited_term'], 10, 3);

				do_action('wpjam_term_list_page_file', $taxonomy);

				$wpjam_list_table	= new WPJAM_Terms_List_Table();
			}
		}elseif($screen_base == 'post'){
			$post_type	= $current_screen->post_type ?? '';

			if($post_type && get_post_type_object($post_type)){
				$edit_form_hook	= $post_type == 'page' ? 'edit_page_form' : 'edit_form_advanced';
				
				add_action($edit_form_hook, 			['WPJAM_Builtin_Page', 'on_edit_post_form'], 99);
				add_action('add_meta_boxes', 			['WPJAM_Builtin_Page', 'on_add_meta_boxes']);

				add_filter('post_updated_messages',		['WPJAM_Builtin_Page', 'filter_post_updated_messages']);
				add_filter('admin_post_thumbnail_html',	['WPJAM_Builtin_Page', 'filter_admin_post_thumbnail_html'], 10, 2);

				add_action('save_post',					['WPJAM_Builtin_Page', 'on_save_post'], 999, 2);

				do_action('wpjam_post_page_file', $post_type);
			}	
		}elseif($screen_base == 'edit' || $screen_base == 'upload'){
			$post_type	= $screen_base == 'upload' ? 'attachment' : $current_screen->post_type ?? '';

			if($post_type && get_post_type_object($post_type)){
				
				do_action('wpjam_post_list_page_file', $post_type);

				$wpjam_list_table	= new WPJAM_Posts_List_Table();
			}
		}elseif($screen_base == 'users'){
			$wpjam_list_table	= new WPJAM_Users_List_Table();
		}

		if(!wp_doing_ajax() && ($summary = apply_filters('wpjam_builtin_page_summary', '', $current_screen))){
			add_filter('wpjam_html', function($html) use($summary) {
				return str_replace('<hr class="wp-header-end">', '<hr class="wp-header-end">'.wpautop($summary), $html);
			});
		}
	}

	public static function data_query_ajax_response(){
		$data_type	= $_POST['data_type'];
		$query_args	= $_POST['query_args'];

		if($data_type == 'post_type'){
			$query_args['posts_per_page']	= $query_args['posts_per_page'] ?? 10;
			$query_args['post_status']		= $query_args['post_status'] ?? 'publish';

			$query	= wpjam_query($query_args);
			$posts	= array_map(function($post){ return wpjam_get_post($post->ID); }, $query->posts);

			wpjam_send_json(['datas'=>$posts]);
		}elseif($data_type == 'taxonomy'){
			$query_args['number']		= $query_args['number'] ?? 10;
			$query_args['hide_empty']	= $query_args['hide_empty'] ?? 0;
			
			$terms	= wpjam_get_terms($query_args, -1);

			wpjam_send_json(['datas'=>$terms]);
		}elseif($data_type == 'model'){
			$model	= $query_args['model'];

			unset($query_args['model']);
			unset($query_args['label_key']);
			unset($query_args['id_key']);

			$query_args['number']	= $query_args['number'] ?? 10;
			
			$query	= $model::Query($query_args);

			wpjam_send_json(['datas'=>$query->datas]);
		}
	}

	public  static function filter_post_updated_messages($messages){
		global $post_type;

		if($post_type == 'page' || $post_type == 'post'){
			return $messages;
		}

		$label_name	= get_post_type_object($post_type)->labels->name;

		$key	= is_post_type_hierarchical($post_type) ? 'page' : 'post';

		$messages[$key]	=  array_map(function($message) use ($label_name){
			if($message == $label_name) {
				return $message;
			}else{
				return str_replace(['文章', '页面', 'post', 'Post'], [$label_name, $label_name, $label_name, ucfirst($label_name)], $message);
			}
		}, $messages[$key]);
		

		return $messages;
	}

	public  static function filter_admin_post_thumbnail_html($content, $post_id){
		if($post_id){
			$pt_obj	= get_post_type_object(get_post($post_id)->post_type);

			if(!empty($pt_obj->thumbnail_size)){
				$content	.= '<p>尺寸：'.$pt_obj->thumbnail_size.'</p>';
			}
		}

		return $content;
	}

	public  static function on_save_post($post_id, $post){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
			return;	
		}

		if($_SERVER['REQUEST_METHOD'] != 'POST'){
			return;	// 提交才可以
		}

		if(!empty($_POST['wp-preview']) && $_POST['wp-preview'] == 'dopreview'){
			return; // 预览不保存
		}

		static $did_save_post_option;

		if(!empty($did_save_post_option)){	// 防止多次重复调用
			return;
		}

		$did_save_post_option = true;

		$post_type		= get_current_screen()->post_type;
		$post_fields	= wpjam_get_post_fields($post_type);
		$post_fields	= apply_filters('wpjam_save_post_fields', $post_fields, $post_id);

		if(empty($post_fields)) {
			return;
		}

		// check_admin_referer('update-post_' .$post_id);

		$value = wpjam_validate_fields_value($post_fields);

		if(is_wp_error($value)){
			wp_die($value);
		}
		
		if($value){
			$custom	= get_post_custom($post_id);

			foreach ($value as $key => $field_value) {
				if($field_value === ''){
					if(isset($custom[$key])){
						delete_post_meta($post_id, $key);
					}
				}else{
					if(empty($custom[$key]) || maybe_unserialize($custom[$key][0]) != $field_value){
						update_post_meta($post_id, $key, $field_value);
					}
				}
			}
		}
	}

	public static function post_options_callback($post, $meta_box){
		$fields			= $meta_box['args']['fields'];
		$fields_type	= $meta_box['args']['context']=='side' ? 'list' : 'table';

		wpjam_fields($fields, array(
			'data_type'		=> 'post_meta',
			'id'			=> $post->ID,
			'fields_type'	=> $fields_type,
			'is_add'		=> get_current_screen()->action == 'add'
		));
	}

	public static function on_add_meta_boxes($post_type){
		$post_options	= wpjam_get_post_options($post_type);

		if($post_options){
			$context	= 'normal';
			if(!function_exists('use_block_editor_for_post_type') || !use_block_editor_for_post_type($post_type)){
				$context	= 'wpjam';
			}

			// 输出日志自定义字段表单
			foreach($post_options as $meta_key => $post_option){
				$post_option = wp_parse_args($post_option, [
					'priority'		=> 'default',
					'context'		=> $context,
					'title'			=> '',
					'callback'		=> ['WPJAM_Builtin_Page', 'post_options_callback'],
					'fields'		=> []
				]);
				
				if($post_option['title']){
					add_meta_box($meta_key, $post_option['title'], $post_option['callback'], $post_type, $post_option['context'], $post_option['priority'], [
						'context'	=> $post_option['context'],
						'fields'	=> $post_option['fields']
					]);
				}
			}
		}
	}

	public static function on_edit_post_form($post){
		// 下面代码 copy 自 do_meta_boxes
		global $wp_meta_boxes;
		
		$page		= get_current_screen()->id;
		$context	= 'wpjam';

		$wpjam_meta_boxes	= $wp_meta_boxes[$page][$context] ?? [];

		if(empty($wpjam_meta_boxes)) {
			return;
		}

		$nav_tab_title	= '';
		$meta_box_count	= 0;

		foreach(['high', 'core', 'default', 'low'] as $priority){
			if(empty($wpjam_meta_boxes[$priority])){
				continue;
			}

			foreach ((array)$wpjam_meta_boxes[$priority] as $meta_box) {
				if(empty($meta_box['id']) || empty($meta_box['title'])){
					continue;
				}

				$meta_box_count++;
				
				$nav_tab_title	.= '<li><a class="nav-tab" href="#tab_'.$meta_box['id'].'">'.$meta_box['title'].'</a></li>';
				
				$meta_box_title	= $meta_box['title'];
			}
		}

		if(empty($nav_tab_title)){
			return;
		}

		echo '<div id="'.htmlspecialchars($context).'-sortables">';
		echo '<div id="'.$context.'" class="postbox tabs">' . "\n";
		
		if($meta_box_count == 1){	
			echo '<h2 class="hndle">';
			echo $meta_box_title;
			echo '</h2>';
		}else{
			echo '<h2 class="nav-tab-wrapper"><ul>';
			echo $nav_tab_title;
			echo '</ul></h2>';
		}

		echo '<div class="inside">';

		foreach (['high', 'core', 'default', 'low'] as $priority) {
			if (!isset($wpjam_meta_boxes[$priority])){
				continue;
			}
			
			foreach ((array) $wpjam_meta_boxes[$priority] as $meta_box) {
				if(empty($meta_box['id']) || empty($meta_box['title'])){
					continue;
				}
				
				echo '<div id="tab_'.$meta_box['id'].'">';
				
				if(isset($post_options[$meta_box['id']])){
					wpjam_fields($post_options[$meta_box['id']]['fields'], array(
						'data_type'		=> 'post_meta',
						'id'			=> $post->ID,
						'fields_type'	=> 'table',
						'is_add'		=> get_current_screen()->action == 'add'
					));
				}else{
					call_user_func($meta_box['callback'], $post, $meta_box);
				}
				
				echo "</div>\n";
			}
		}

		echo "</div>\n";

		echo "</div>\n";
		echo "</div>";
	}

	public static function filter_term_updated_messages($messages){
		global $taxonomy;

		if($taxonomy == 'post_tag' || $taxonomy == 'category'){
			return $messages;
		}

		$label_name	= get_taxonomy($taxonomy)->labels->name;

		$messages[$taxonomy]	= array_map(function($message) use ($label_name){
			if($message == $label_name){
				return $message;
			}else{
				return str_replace(['项目', 'Item'], [$label_name, ucfirst($label_name)], $message);
			}
		}, $messages['_item']);

		return $messages;
	}

	public static function filter_taxonomy_parent_dropdown_args($args, $taxonomy, $action_type){
		$tax_obj	= get_taxonomy($taxonomy);
		$levels		= $tax_obj->levels;

		if($levels > 1){
			$args['depth']	= $levels - 1;

			if($action_type == 'edit'){
				$term_id		= $args['exclude_tree'];
				$term_levels	= count(get_ancestors($term_id, $taxonomy, 'taxonomy'));
				$child_levels	= $term_levels;

				$children	= get_term_children($term_id, $taxonomy);
				if($children){
					$child_levels = 0;

					foreach($children as $child){
						$new_child_levels	= count(get_ancestors($child, $taxonomy, 'taxonomy'));
						if($child_levels	< $new_child_levels){
							$child_levels	= $new_child_levels;
						}
					}
				}

				$redueced	= $child_levels - $term_levels;

				if($redueced < $args['depth']){
					$args['depth']	-= $redueced;
				}else{
					$args['parent']	= -1;
				}
			}
		}

		return $args;
	}

	public  static function on_add_form_fields($taxonomy){
		$fields	= wpjam_get_term_options($taxonomy, 'add') ?: [];

		wpjam_fields($fields, [
			'data_type'		=> 'term_meta',
			'fields_type'	=> 'div',
			'item_class'	=> 'form-field',
			'is_add'		=> true
		]);
	}

	public  static function on_edit_form_fields($term){
		$fields	= wpjam_get_term_options($term->taxonomy, 'edit') ?: [];
			
		wpjam_fields($fields, [
			'data_type'		=> 'term_meta',
			'fields_type'	=> 'tr',
			'item_class'	=> 'form-field',
			'id'			=> $term->term_id
		]);
	}

	public  static function filter_pre_insert_term($term, $taxonomy){
		if(wp_doing_ajax() && $_POST['action'] == 'add-tag'){
			if($fields = wpjam_get_term_options($taxonomy)){
				$value	= wpjam_validate_fields_value($fields);

				if(is_wp_error($value)){
					return $value;
				}
			}
		}

		return $term;
	}

	public  static function on_created_term($term_id, $tt_id, $taxonomy){
		if(wp_doing_ajax() && $_POST['action'] == 'add-tag'){
			if($fields	= wpjam_get_term_options($taxonomy)){
				if($value = wpjam_validate_fields_value($fields)){
					foreach ($value as $key => $field_value) {
						if($field_value){
							update_term_meta($term_id, $key, $field_value);
						}
					}
				}
			}
		}
	}

 	public static function on_edited_term($term_id, $tt_id, $taxonomy){
 		if(!wp_doing_ajax() && $fields = wpjam_get_term_options($taxonomy)){
			$value	= wpjam_validate_fields_value($fields);

			if(is_wp_error($value)){
				wp_die($value);
			}elseif($value){
				foreach ($value as $key => $field_value) {
					if($field_value){
						update_term_meta($term_id, $key, $field_value);
					}else{
						if(metadata_exists('term', $term_id, $key)){
							delete_term_meta($term_id, $key);	
						}
					}
				}
			}
		}
	}
}