<?php
class WPJAM_Setting{
	protected static $option_settings	= [];

	public static function register($option_name, $args=[]){
		self::$option_settings[$option_name]	= $args;
	}

	public static function get_option_setting($option_name){
		$option_setting	= apply_filters(wpjam_get_filter_name($option_name,'setting'), []);

		if(!$option_setting){

			if(self::$option_settings && !empty(self::$option_settings[$option_name])){
				$option_setting		= self::$option_settings[$option_name];
			}else{
				$option_settings	= apply_filters('wpjam_settings', [], $option_name);

				if(!$option_settings || empty($option_settings[$option_name])) {
					return false;
				}

				$option_setting	= $option_settings[$option_name];
			}	
		}

		if(empty($option_setting['sections'])){	// 支持简写
			if(isset($option_setting['fields'])){
				$fields		= $option_setting['fields'];
				unset($option_setting['fields']);
				$option_setting['sections']	= [$option_name => compact('fields')];
			}else{
				$option_setting['sections']	= $option_setting;
			}
		}

		return wp_parse_args($option_setting, [
			'option_group'	=> $option_name, 
			'option_page'	=> $option_name, 
			'option_type'	=> 'array', 	// array：设置页面所有的选项作为一个数组存到 options 表， single：每个选项单独存到 options 表。
			'capability'	=> 'manage_options',
			'ajax'			=> true,
			'sections'		=> []
		]);
	}

	public static function get_option($option_name, $blog_id=0){
		if(is_multisite()){
			if(is_network_admin()){
				return get_site_option($option_name) ?: [];
			}else{
				if($blog_id){
					$option	= get_blog_option($blog_id, $option_name) ?: [];
				}else{
					$option	= get_option($option_name) ?: [];	
				}

				if(apply_filters('wpjam_option_use_site_default', false, $option_name)){
					$site_option	= get_site_option($option_name) ?: [];
					$option			= $option + $site_option;
				}

				return $option;
			}
		}else{
			return get_option($option_name) ?: [];
		}
	}

	public static function update_option($option_name, $option_value, $blog_id=0){
		if(is_multisite()){
			if(is_network_admin()){
				return update_site_option($option_name, $option_value);
			}else{
				if($blog_id){
					return update_blog_option($blog_id, $option_name, $option_value);
				}else{
					return update_option($option_name, $option_value);	
				}
			}
		}else{
			return update_option($option_name, $option_value);
		}
	}

	public static function get_setting($option_name, $setting_name, $blog_id=0){
		$option_value	= is_string($option_name) ? self::get_option($option_name, $blog_id) : $option_name;

		if($option_value && isset($option_value[$setting_name])){
			$value	= $option_value[$setting_name];

			if($value && is_string($value)){
				return  str_replace("\r\n", "\n", trim($value));
			}else{
				return $value;
			}
		}else{
			return null;
		}
	}

	public static function update_setting($option_name, $setting_name, $setting_value, $blog_id=0){
		$option_value	= self::get_option($option_name, $blog_id);

		$option_value[$setting_name]	= $setting_value;

		return self::update_option($option_name, $option_value, $blog_id);
	}

	public static function delete_setting($option_name, $setting_name, $blog_id=0){
		$option_value	= self::get_option($option_name, $blog_id);

		if($option_value && isset($option_value[$setting_name])){
			unset($option_value[$setting_name]);
		}

		return self::update_option($option_name, $option_value, $blog_id);
	}
}

class WPJAM_Post{
	protected static $post_types	= [];
	protected static $post_options	= [];

	public static function register_post_type($post_type, $args=[]){
		self::$post_types[$post_type]	= $args;
	}

	public static function get_post_types(){
		return apply_filters('wpjam_post_types', self::$post_types);
	}

	public static function register_post_option($meta_box, $args=[]){
		self::$post_options[$meta_box]	= $args;
	}

	public static function get_post_options($post_type){
		$post_type_options	= [];
		
		if($post_options = apply_filters('wpjam_post_options', self::$post_options, $post_type)){
			foreach($post_options as $meta_key => $post_option){
				$post_option = wp_parse_args($post_option, [
					'post_types'	=> 'all',
					'post_type'		=> ''
				]);

				if($post_option['post_type'] && $post_option['post_types'] == 'all'){
					$post_option['post_types'] = [$post_option['post_type']];
				}

				if($post_option['post_types'] == 'all' || in_array($post_type, $post_option['post_types'])){
					$post_type_options[$meta_key] = $post_option;
				}
			}
		}

		return apply_filters('wpjam_'.$post_type.'_post_options', $post_type_options);
	}

	public static function get_post_fields($post_type){
		if($post_options = self::get_post_options($post_type)) {
			return call_user_func_array('array_merge', array_column(array_values($post_options), 'fields'));
		}else{
			return [];
		}
	}

	public static function validate($post_id, $post_type='', $action=''){
		$post	= self::get_post($post_id);

		if(!$post){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		if($post_type && $post_type != 'any' && $post_type != $post->post_type){
			return new WP_Error('invalid_post_type', '无效的文章类型');
		}

		return $post;
	}

	public static function get_views($post_id, $addon=false){
		$views	= intval(get_post_meta($post_id, 'views', true));

		if($addon){
			$views	= $views + apply_filters('wpjam_post_views_addon', 0, $post_id);
		}

		return intval($views);
	}

	public static function update_views($post_id){
		static $post_viewed;

		if(!empty($post_viewed)){
			return;
		}

		$post_viewed	= true;

		$views	= self::get_views($post_id);
		$views++;
		
		return update_post_meta($post_id, 'views', $views);
	}

	public static function get_content($post=null, $raw=false){
		$content	= get_the_content('', false, $post);

		if(!$raw){
			$content	= apply_filters('the_content', $content);
			$content	= str_replace(']]>', ']]&gt;', $content);
		}

		return $content;
	}

	public static function get_excerpt($post=null, $excerpt_length=0, $excerpt_more=''){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return '';
		}

		if($excerpt = $post->post_excerpt){
			return wp_strip_all_tags($excerpt, true);	
		}

		$excerpt	= get_the_content('', false, $post);
		
		$excerpt	= strip_shortcodes($excerpt);
		$excerpt	= function_exists('excerpt_remove_blocks') ? excerpt_remove_blocks($excerpt) : $excerpt;

		if(has_filter('the_content', 'wp_filter_content_tags')){
			remove_filter('the_content', 'wp_filter_content_tags');
			$filter_content_tags_readd	= true;
		}else{
			$filter_content_tags_readd	= false;
		}

		$excerpt	= apply_filters('the_content', $excerpt);
		$excerpt	= str_replace(']]>', ']]&gt;', $excerpt);

		if($filter_content_tags_readd){
			add_filter('the_content', 'wp_filter_content_tags');
		}
		
		$excerpt	= wp_strip_all_tags($excerpt, true);
		
		$excerpt_length	= $excerpt_length ?: apply_filters('excerpt_length', 200);
		$excerpt_more	= $excerpt_more ?: apply_filters('excerpt_more', ' '.'&hellip;');
		
		return mb_strimwidth($excerpt, 0, $excerpt_length, $excerpt_more, 'utf-8');
	}

	public static function get_thumbnail_url($post=null, $size='thumbnail', $crop=1){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return '';
		}

		if(post_type_supports($post->post_type, 'thumbnail') && has_post_thumbnail($post)){
			$thumbnail_url	= wp_get_attachment_image_url(get_post_thumbnail_id($post), 'full');
		}else{
			$thumbnail_url	= apply_filters('wpjam_post_thumbnail_url', '', $post);
		}

		if($thumbnail_url && empty($size)){
			$pt_obj	= get_post_type_object($post->post_type);
			$size	= !empty($pt_obj->thumbnail_size) ? $pt_obj->thumbnail_size : 'thumbnail';
		}

		return $thumbnail_url ? wpjam_get_thumbnail($thumbnail_url, $size, $crop) : '';
	}

	public static function get_first_image_url($content=null, $size='full'){
		if(is_null($content) || is_object($content)){
			$post	= $content;
			
			if(post_password_required($post)){
				return '';
			}

			$content	= $post->post_content;
		}

		if($content){
			preg_match_all( '/class=[\'"].*?wp-image-([\d]*)[\'"]/i', $content, $matches );
			if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	
				$image_id = $matches[1][0];
				return wp_get_attachment_image_url($image_id, $size);
			}

			preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches);
			if( $matches && isset($matches[1]) && isset($matches[1][0]) ){	  
				return wpjam_get_thumbnail($matches[1][0], $size);
			}
		}
			
		return '';
	}

	public static function get_author($post=null, $size=96){
		$post	= get_post($post);
		
		if(!($post instanceof WP_Post)){
			return null;
		}

		$author_id	= $post->post_author;
		$author		= get_userdata($author_id);

		if($author){
			return [
				'id'		=> intval($author_id),
				'name'		=> $author->display_name,
				'avatar'	=> get_avatar_url($author_id, 200),
			];
		}else{
			return null;
		}
	}

	public static function get_related_query($post=null, $number=5, $post_type=null){
		$post	= get_post($post);

		if(!($post instanceof WP_Post)){
			return [];
		}

		$query_args	= [
			'cache_it'				=> true,
			'no_found_rows'			=> true,
			'ignore_sticky_posts'	=> true,
			'cache_results'			=> true,
			'related_query'			=> true,
			'post_status'			=> 'publish',
		];

		$query_args['post__not_in']		= [$post->ID];
		$query_args['post_type']		= $post_type ?: $post->post_type;
		$query_args['posts_per_page']	= $number ?: 5;

		$term_taxonomy_ids = [];

		if($taxonomies = get_object_taxonomies($post->post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($terms	= get_the_terms($post->ID, $taxonomy)){
					$term_taxonomy_ids = array_merge($term_taxonomy_ids, array_column($terms, 'term_taxonomy_id'));
				}
			}

			$term_taxonomy_ids	= array_unique(array_filter($term_taxonomy_ids));
		}

		$query_args['term_taxonomy_ids']	= $term_taxonomy_ids;
		
		return new WP_Query($query_args);
	}

	public static function get_related($post_id=null, $args=[]){
		$post	= get_post($post_id);

		if(!($post instanceof WP_Post)){
			return [];
		}

		$post_type	= $args['post_type'] ?? null;
		$number		= $args['number'] ?? 5;

		$related_query	= self::get_related_query($post_id, $number, $post_type);

		return self::get_list($related_query, $args);
	}

	public static function get_list($wpjam_query, $args=[]){
		$parse_for_json	= $args['parse_for_json'] ?? true;

		if($parse_for_json){
			$args	= wp_parse_args($args, [
				'size'		=> '',
				'filter'	=> 'wpjam_related_post_json'
			]);

			$posts_json	= [];
		}else{
			$args	= wp_parse_args($args, [
				'title'			=> '',
				'div_id'		=> '',
				'class'			=> '', 
				'thumb'			=> true,	
				'excerpt'		=> false, 
				'size'			=> 'thumbnail', 
				'crop'			=> true, 
				'thumb_class'	=> 'wp-post-image'
			]);

			$output = '';
		}

		if($wpjam_query->have_posts()){

			while($wpjam_query->have_posts()){
				$wpjam_query->the_post();

				if($parse_for_json){
					global $post;
					
					$post_json	= [];

					$post_json['id']		= $post->ID;
					$post_json['timestamp']	= intval(strtotime(get_gmt_from_date($post->post_date)));
					$post_json['time']		= wpjam_human_time_diff($post_json['timestamp']);
					
					$post_json['title']		= html_entity_decode(get_the_title($post));

					if(is_post_type_viewable($post->post_type)){
						$post_json['name']		= urldecode($post->post_name);
						$post_json['post_url']	= str_replace(home_url(), '', get_permalink($post->ID));
					}

					if(post_type_supports($post->post_type, 'author')){
						$post_json['author']	= self::get_author($post);
					}

					if(post_type_supports($post->post_type, 'excerpt')){
						$post_json['excerpt']	= html_entity_decode(get_the_excerpt($post));
					}

					$post_json['thumbnail']		= self::get_thumbnail_url($post, $args['size']);

					$post_json		= apply_filters($args['filter'], $post_json, $post->ID, $args);

					$posts_json[]	= $post_json;
				}else{
					$li = get_the_title();

					if($args['thumb'] || $args['excerpt']){
						$li = '<h4>'.$li.'</h4>';

						if($args['thumb']){
							$li = wpjam_get_post_thumbnail(null, $args['size'], $args['crop'], $args['thumb_class'])."\n".$li;
						}

						if($args['excerpt']){
							$li .= "\n".wpautop(get_the_excerpt());
						}
					}

					if(!is_singular() || (is_singular() && get_queried_object_id() != get_the_ID())) {
						$li = '<a href="'.get_permalink().'" title="'.the_title_attribute(['echo'=>false]).'">'.$li.'</a>';
					}

					$output .=	'<li>'.$li.'</li>'."\n";
				}
			}
		}

		wp_reset_postdata();

		if($parse_for_json){
			return $posts_json;
		}else{
			if($args['thumb']){
				$args['class']	= $args['class'].' has-thumb';
			}
			
			$class	= $args['class'] ? ' class="'.$args['class'].'"' : '';
			$output = '<ul'.$class.'>'."\n".$output.'</ul>'."\n";

			if($args['title']){
				$output	= '<h3>'.$args['title'].'</h3>'."\n".$output;
			}

			if($args['div_id']){
				$output	= '<div id="'.$args['div_id'].'">'."\n".$output.'</div>'."\n";
			}

			return $output;	
		}
	}

	public static function get($post_id){
		$args	= ['content_required'=>true];

		if(is_admin()){
			$args['raw_content']	= true;
		}

		return self::parse_for_json($post_id, $args);
	}

	public static function insert($data){
		$data['post_status']	= $data['post_status']	?? 'publish';
		$data['post_author']	= $data['post_author']	?? get_current_user_id();
		$data['post_date']		= $data['post_date']	?? get_date_from_gmt(date('Y-m-d H:i:s', time()));

		return wp_insert_post($data, true);
	}

	public static function update($post_id, $data){
		if(!get_post($post_id)){
			return new WP_Error('post_not_exists', '文章不存在');
		}
		
		$data['ID'] = $post_id;

		return wp_update_post($data, true);
	}

	public static function delete($post_id){
		if(!get_post($post_id)){
			return new WP_Error('post_not_exists', '文章不存在');
		}

		$result		= wp_delete_post($post_id, true);

		if(!$result){
			return new WP_Error('delete_failed', '删除失败');
		}else{
			return true;
		}
	}

	public static function parse_for_json($post_id, $args=[]){
		$args	= wp_parse_args($args, array(
			'thumbnail_size'	=> '',
			'content_required'	=> false,
			'raw_content'		=> false
		));

		if(empty($post_id))	{
			return null;
		}

		global $post;

		$post	= self::get_post($post_id);

		if(empty($post)){
			return null;
		}

		setup_postdata($post);

		$post_id	= intval($post->ID);
		$post_type	= $post->post_type;

		if(!post_type_exists($post_type)){
			return [];
		}

		$post_json	= [];

		$post_json['id']		= $post_id;
		$post_json['post_type']	= $post_type;
		$post_json['status']	= $post->post_status;

		if($post->post_password){
			$post_json['password_protected']	= true;
			if(post_password_required($post)){
				$post_json['passed']	= false;
			}else{
				$post_json['passed']	= true;
			}
		}else{
			$post_json['password_protected']	= false;
		}

		$post_json['timestamp']			= intval(strtotime(get_gmt_from_date($post->post_date)));
		$post_json['time']				= wpjam_human_time_diff($post_json['timestamp']);
		$post_json['modified_timestamp']= intval(strtotime($post->post_modified_gmt));
		$post_json['modified']			= wpjam_human_time_diff($post_json['modified_timestamp']);

		if(is_post_type_viewable($post_type)){
			$post_json['name']		= urldecode($post->post_name);
			$post_json['post_url']	= str_replace(home_url(), '', get_permalink($post_id));
		}

		$post_json['title']		= '';
		if(post_type_supports($post_type, 'title')){
			$post_json['title']		= html_entity_decode(get_the_title($post));

			if(is_singular($post_type)){
				$post_json['page_title']	= $post_json['title'];
				$post_json['share_title']	= $post_json['title'];
			}
		}

		$post_json['thumbnail']		= self::get_thumbnail_url($post, $args['thumbnail_size']);

		if(post_type_supports($post_type, 'author')){
			$post_json['author']	= self::get_author($post);
		}
		
		if(post_type_supports($post_type, 'excerpt')){
			$post_json['excerpt']	= html_entity_decode(get_the_excerpt($post));
		}

		if(post_type_supports($post_type, 'page-attributes')){
			$post_json['menu_order']	= intval($post->menu_order);
		}

		if(post_type_supports($post_type, 'post-formats')){
			$post_json['format']	= get_post_format($post) ?: '';
		}

		if($taxonomies = get_object_taxonomies($post_type)){
			foreach ($taxonomies as $taxonomy) {
				if($taxonomy != 'post_format'){
					if($terms	= get_the_terms($post_id, $taxonomy)){
						array_walk($terms, function(&$term) use ($taxonomy){ $term 	= wpjam_get_term($term, $taxonomy);});
						$post_json[$taxonomy]	= $terms;
					}else{
						$post_json[$taxonomy]	= [];
					}		
				}
			}
		}

		if(is_singular($post_type) || $args['content_required']){
			if(post_type_supports($post_type, 'editor')){
				if($args['raw_content']){
					$post_json['raw_content']	= self::get_content($post, true);
				}

				$post_json['content']	= self::get_content($post);	

				global $page, $numpages, $multipage;

				$post_json['multipage']	= boolval($multipage);

				if($multipage){
					$post_json['numpages']	= $numpages;
					$post_json['page']		= $page;
				}
			}

			if(is_singular($post_type)){
				self::update_views($post_id);
			}
		}
		
		$post_json['views']	= self::get_views($post_id);

		return apply_filters('wpjam_post_json', $post_json, $post_id, $args);
	}

	public static function get_by_ids($post_ids){
		return self::update_caches($post_ids);
	}
	
	public static function update_caches($post_ids, $args=[]){
		if($post_ids){
			$post_ids 	= array_filter($post_ids);
			$post_ids 	= array_unique($post_ids);
		}

		if(empty($post_ids)) {
			return [];
		}

		$update_term_cache	= $args['update_post_term_cache'] ?? true;
		$update_meta_cache	= $args['update_post_meta_cache'] ?? true;

		_prime_post_caches($post_ids, $update_term_cache, $update_meta_cache);

		if(function_exists('wp_cache_get_multiple')){

			$cache_values	= wp_cache_get_multiple($post_ids, 'posts');

			foreach ($post_ids as $post_id) {
				if(!isset($cache_values[$post_id])){
					wp_cache_add($post_id, false, 'posts', 10);	// 防止大量 SQL 查询。
				}
			}

			return $cache_values;
		}else{
			$cache_values	= [];

			foreach ($post_ids as $post_id) {
				$cache	= wp_cache_get($post_id, 'posts');

				if($cache !== false){
					$cache_values[$post_id]	= $cache;
				}
			}

			return $cache_values;
		}
	}

	public static function get_post($post, $output=OBJECT, $filter='raw'){
		if($post && is_numeric($post)){
			$found	= false;
			$cache	= wp_cache_get($post, 'posts', false, $found);

			if($found){
				if(is_wp_error($cache)){
					return $cache;
				}elseif(!$cache){
					return null;
				}
			}
		}

		return get_post($post, $output, $filter);
	}

	public static function init(){
		foreach (self::get_post_types() as $post_type=>$post_type_args) {
			$post_type_args	= wp_parse_args($post_type_args, [
				'public'			=> true,
				'show_ui'			=> true,
				'hierarchical'		=> false,
				'rewrite'			=> true,
				'permastruct'		=> false,
				'thumbnail_size'	=> '',
				// 'capability_type'	=> $post_type,
				// 'map_meta_cap'		=> true,
				'supports'			=> ['title'],
				'taxonomies'		=> [],
			]);

			if(empty($post_type_args['taxonomies'])){
				unset($post_type_args['taxonomies']);
			}

			$permastruct	= $post_type_args['permastruct'];

			if($post_type_args['hierarchical']){
				$post_type_args['supports'][]	= 'page-attributes';

				if($permastruct && (strpos($permastruct, '%post_id%') || strpos($permastruct, '%'.$post_type.'_id%'))){
					$post_type_args['permastruct']	= $permastruct	= false;
				}
			}else{
				if($permastruct && (strpos($permastruct, '%post_id%') || strpos($permastruct, '%'.$post_type.'_id%'))){
					$post_type_args['query_var']	= false;
				}
			}

			if($permastruct){
				if(empty($post_type_args['rewrite'])){
					$post_type_args['rewrite']	= true;
				}
			}

			if($post_type_args['rewrite']){
				if(is_array($post_type_args['rewrite'])){
					$post_type_args['rewrite']	= wp_parse_args($post_type_args['rewrite'], ['with_front'=>false, 'feeds'=>false]);
				}else{
					$post_type_args['rewrite']	= ['with_front'=>false, 'feeds'=>false];
				}
			}

			register_post_type($post_type, $post_type_args);

			if($permastruct){
				global $wp_rewrite;
				
				if(strpos($permastruct, '%post_id%') || strpos($permastruct, '%'.$post_type.'_id%')){
					$wp_rewrite->extra_permastructs[$post_type]['struct']	= str_replace('%post_id%', '%'.$post_type.'_id%', $permastruct);

					add_rewrite_tag('%'.$post_type.'_id%', '([0-9]+)', 'post_type='.$post_type.'&p=');

					remove_rewrite_tag('%'.$post_type.'%');
				}elseif(strpos($permastruct, '%postname%')){
					$wp_rewrite->extra_permastructs[$post_type]['struct'] = $permastruct;
				}
			}
		}
	}

	public static function filter_post_password_required($required, $post){
		if(!$required){
			return $required;
		}

		$password	= $_REQUEST['post_password'] ?? '';

		if(empty($password)){
			return $required;
		}

		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher	= new PasswordHash( 8, true );
		$hash	= wp_unslash($password);

		if(0 !== strpos($hash, '$P$B')) {
			return true;
		}
		
		return ! $hasher->CheckPassword($post->post_password, $hash);
	}

	public static function filter_posts_clauses($clauses, $wp_query){
		global $wpdb;

		if($wp_query->get('related_query')){
			if($term_taxonomy_ids	= $wp_query->get('term_taxonomy_ids')){
				$clauses['fields']	.= ", count(tr.object_id) as cnt";
				$clauses['join']	.= "INNER JOIN {$wpdb->term_relationships} AS tr ON {$wpdb->posts}.ID = tr.object_id";
				$clauses['where']	.= " AND tr.term_taxonomy_id IN (".implode(",",$term_taxonomy_ids).")";
				$clauses['groupby']	.= " tr.object_id";
				$clauses['orderby']	= " cnt DESC, {$wpdb->posts}.post_date_gmt DESC";	
			}
		}elseif(($orderby = $wp_query->get('orderby')) && in_array($orderby, ['views', 'favs', 'likes'])){
			$order		= $wp_query->get('order') ?: 'DESC';

			$clauses['fields']	.= ", (COALESCE(jam_pm.meta_value, 0)+0) as {$orderby}";
			$clauses['join']	.= "LEFT JOIN {$wpdb->postmeta} jam_pm ON {$wpdb->posts}.ID = jam_pm.post_id AND jam_pm.meta_key = '{$orderby}' ";
			$clauses['orderby']	= "{$orderby} {$order}, " . $clauses['orderby'];
		}

		return $clauses;
	}

	public static function filter_post_type_link($post_link, $post){
		$post_type	= $post->post_type;

		if(empty(get_post_type_object($post_type)->permastruct)){
			return $post_link;
		}

		$post_link	= str_replace('%'.$post_type.'_id%', $post->ID, $post_link);

		if(strpos($post_link, '%') === false){
			return $post_link;
		}

		$taxonomies = get_taxonomies(['object_type'=>[$post_type]], 'objects');

		if(!$taxonomies){
			return $post_link;
		}

		foreach ($taxonomies as $taxonomy=>$taxonomy_object) {
			if($taxonomy_rewrite = $taxonomy_object->rewrite){

				if(strpos($post_link, '%'.$taxonomy_rewrite['slug'].'%') === false){
					continue;
				}

				if($terms = get_the_terms($post->ID, $taxonomy)){
					$post_link	= str_replace('%'.$taxonomy_rewrite['slug'].'%', current($terms)->slug, $post_link);
				}else{
					$post_link	= str_replace('%'.$taxonomy_rewrite['slug'].'%', $taxonomy, $post_link);
				}
			}
		}

		return $post_link;
	}
}

class WPJAM_Term{
	protected static $taxonomies	= [];
	protected static $term_options	= [];

	public static function register_taxonomy($taxonomy, $args=[]){
		self::$taxonomies[$taxonomy]	= $args;
	}

	public static function get_taxonomies(){
		return apply_filters('wpjam_taxonomies', self::$taxonomies);
	}

	public static function register_term_option($key, $args=[]){
		self::$term_options[$key]	= $args;
	}

	public static function get_term_options($taxonomy, $action=''){
		$taxonomy_options	= [];

		if($term_options = apply_filters('wpjam_term_options', self::$term_options, $taxonomy)){
			foreach ($term_options as $key => $term_option) {
				$term_option	= wp_parse_args( $term_option, [
					'taxonomies'	=> 'all',
					'taxonomy'		=> '',
					'action'		=> ''
				]);

				if($action && $term_option['action'] && $action != $term_option['action']){
					continue;
				}

				if($term_option['taxonomy'] && $term_option['taxonomies'] == 'all'){
					$term_option['taxonomies'] = [$term_option['taxonomy']];
				}

				if($term_option['taxonomies'] == 'all' || in_array($taxonomy, $term_option['taxonomies'])){
					$taxonomy_options[$key]	= $term_option;
				}
			}
		}

		return apply_filters('wpjam_'.$taxonomy.'_term_options', $taxonomy_options);
	}

	public static function get_thumbnail_url($term=null, $size='full', $crop=1){
		$term	= $term ?: get_queried_object();
		$term	= get_term($term);

		if(!$term) {
			return '';
		}

		$thumbnail_url	= apply_filters('wpjam_term_thumbnail_url', '', $term);

		return $thumbnail_url ? wpjam_get_thumbnail($thumbnail_url, $size, $crop) : '';
	}

	public static function get_children($term, $children_terms=[], $max_depth=-1, $depth=0){
		$term	= self::parse_for_json($term);

		if(is_wp_error($term)){
			return $term;
		}

		$term['children'] = [];

		if($children_terms){
			$term_id	= $term['id'];

			if(($max_depth == 0 || $max_depth > $depth+1) && isset($children_terms[$term_id])){
				foreach($children_terms[$term_id] as $child){
					$term['children'][]	= self::get_children($child, $children_terms, $max_depth, $depth + 1);
				}
			} 
		}

		return $term;
	}

	/**
	* $max_depth = -1 means flatly display every element.
	* $max_depth = 0 means display all levels.
	* $max_depth > 0 specifies the number of display levels.
	*
	*/
	public static function get_terms($args, $max_depth=-1){
		$taxonomy	= $args['taxonomy'];
		$parent		= 0;

		$raw_args	= $args;

		if(isset($args['parent']) && ($max_depth != -1 && $max_depth != 1)){
			$parent		= $args['parent'];
			unset($args['parent']);
		}

		$terms = get_terms($args) ?: [];

		if(is_wp_error($terms) || empty($terms)){
			return $terms;
		}

		if($max_depth == -1){
			foreach ($terms as &$term) {
				$term = self::parse_for_json($term, $taxonomy); 

				if(is_wp_error($term)){
					return $term;
				}
			}
		}else{
			$top_level_terms	= [];
			$children_terms		= [];

			foreach($terms as $term){
				if(empty($term->parent)){
					if($parent){
						if($term->term_id == $parent){
							$top_level_terms[] = $term;
						}
					}else{
						$top_level_terms[] = $term;
					}
				}else{
					$children_terms[$term->parent][] = $term;
				}
			}

			if($terms = $top_level_terms){
				foreach ($terms as &$term) {
					if($max_depth == 1){
						$term = self::parse_for_json($term, $taxonomy);
					}else{
						$term = self::get_children($term, $children_terms, $max_depth, 0);	
					}

					if(is_wp_error($term)){
						return $term;
					}
				}
			}
		}
	
		return apply_filters('wpjam_terms', $terms, $raw_args, $max_depth);
	}

	public static function flatten($terms, $depth=0){
		$terms_flat	= [];

		if($terms){
			foreach ($terms as $term){
				$term['name']	= str_repeat('&nbsp;', $depth*3).$term['name'];
				$terms_flat[]	= $term;

				if(!empty($term['children'])){
					$depth++;

					$terms_flat	= array_merge($terms_flat, self::flatten($term['children'], $depth));

					$depth--;
				}
			}
		}

		return $terms_flat;
	}

	public static function get($term_id){
		$term	= get_term($term_id);

		if(is_wp_error($term) || empty($term)){
			return [];
		}else{
			return self::parse_for_json($term, $term->taxonomy);
		}
	}

	public static function insert($data){
		$taxonomy		= $data['taxonomy']		?? '';

		if(empty($taxonomy)){
			return new WP_Error('empty_taxonomy', '分类模式不能为空');
		}

		$name			= $data['name']			?? '';
		$parent			= $data['parent']		?? 0;
		$slug			= $data['slug']			?? '';
		$description	= $data['description']	?? '';

		if(term_exists($name, $taxonomy)){
			return new WP_Error('term_exists', '相同名称的'.get_taxonomy($taxonomy)->label.'已存在。');
		}

		$term	= wp_insert_term($name, $taxonomy, compact('parent','slug','description'));

		if(is_wp_error($term)){
			return $term;
		}

		$term_id	= $term['term_id'];

		$meta_input	= $data['meta_input']	?? [];

		if($meta_input){
			foreach($meta_input as $meta_key => $meta_value) {
				update_term_meta($term_id, $meta_key, $meta_value);
			}
		}

		return $term_id;
	}

	public static function update($term_id, $data){
		$taxonomy		= $data['taxonomy']	?? '';

		if(empty($taxonomy)){
			return new WP_Error('empty_taxonomy', '分类模式不能为空');
		}

		$term	= self::get_term($term_id, $taxonomy);

		if(is_wp_error($term)){
			return $term;
		}

		if(isset($data['name'])){
			$exist	= term_exists($data['name'], $taxonomy);

			if($exist){
				$exist_term_id	= $exist['term_id'];

				if($exist_term_id != $term_id){
					return new WP_Error('term_name_duplicate', '相同名称的'.get_taxonomy($taxonomy)->label.'已存在。');
				}
			}
		}

		$term_args = [];

		$term_keys = ['name', 'parent', 'slug', 'description'];

		foreach($term_keys as $key) {
			$value = $data[$key] ?? null;
			if (is_null($value)) {
				continue;
			}

			$term_args[$key] = $value;
		}

		if(!empty($term_args)){
			$term =	wp_update_term($term_id, $taxonomy, $term_args);
			if(is_wp_error($term)){
				return $term;	
			}
		}

		$meta_input		= $data['meta_input']	?? [];

		if($meta_input){
			foreach($meta_input as $meta_key => $meta_value) {
				update_term_meta($term['term_id'], $meta_key, $meta_value);
			}
		}

		return true;
	}

	public static function delete($term_id){
		$term	= get_term($term_id);

		if(is_wp_error($term) || empty($term)){
			return $term;
		}

		return wp_delete_term($term_id, $term->taxonomy);
	}

	public static function merge($term_id, $merge_to, $delete=true){
		$term	= get_term($term_id);

		if(is_wp_error($term) || empty($term)){
			return $term;
		}

		$merge_to_term	= get_term($merge_to);

		if(is_wp_error($merge_to_term) || empty($merge_to_term)){
			return $merge_to_term;
		}

		$taxonomy_obj	= get_taxonomy($term->taxonomy);
		$post_types		= $taxonomy_obj->object_type;

		$query	= new WP_Query([
			'post_type'		=> $post_types,
			'post_status'	=> 'all',
			'fields'		=> 'ids',
			'posts_per_page'=> -1,
			'tax_query'		=> [
				['taxonomy'=>$term->taxonomy, 'terms'=>[$term_id], 'field'=>'id']
			]
		]);

		if($query->posts){
			foreach($query->posts as $post_id) {
				wp_set_post_terms($post_id, $merge_to, $merge_to_term->taxonomy, true);
			}
		}

		if($delete){
			return self::delete($term_id);
		}else{
			return true;
		}
	}
	
	public static function parse_for_json($term, $taxonomy=null){
		$term	= self::get_term($term, $taxonomy);
		
		if(is_wp_error($term) || empty($term) || ($taxonomy && $taxonomy != $term->taxonomy)){
			if($taxonomy){
				return new WP_Error('illegal_'.$taxonomy.'_id', $taxonomy.'_id 无效');	
			}else{
				return new WP_Error('illegal_term_id', 'term_id 无效');
			}
		}

		$term_json	= [];

		$term_json['id']		= $term_id	= $term->term_id;
		$term_json['taxonomy']	= $taxonomy	= $term->taxonomy;
		$term_json['name']		= $term->name;

		if(get_queried_object_id() == $term_id){
			$term_json['page_title']	= $term->name;
			$term_json['share_title']	= $term->name;
		}

		$taxonomy_obj	= get_taxonomy($taxonomy);

		if($taxonomy_obj->public || $taxonomy_obj->publicly_queryable || $taxonomy_obj->query_var){
			$term_json['slug']		= $term->slug;
		}
		
		$term_json['count']			= intval($term->count);
		$term_json['description']	= $term->description;
		$term_json['parent']		= $term->parent;
		
		return apply_filters('wpjam_term_json', $term_json, $term_id);
	}

	public static function get_by_ids($post_ids){
		return self::update_caches($post_ids);
	}

	public static function update_caches($term_ids, $args=[]){
		if($term_ids){
			$term_ids 	= array_filter($term_ids);
			$term_ids 	= array_unique($term_ids);
		}

		if(empty($term_ids)) {
			return [];
		}

		$update_meta_cache	= $args['update_meta_cache'] ?? true;

		_prime_term_caches($term_ids, $update_meta_cache);

		if(function_exists('wp_cache_get_multiple')){
			$cache_values	= wp_cache_get_multiple($post_ids, 'terms');

			foreach ($term_ids as $term_id) {
				if(!isset($cache_values[$term_id])){
					wp_cache_add($term_id, false, 'terms', 10);	// 防止大量 SQL 查询。
				}
			}

			return $cache_values;
		}else{
			$cache_values	= [];

			foreach ($term_ids as $term_id) {
				$cache	= wp_cache_get($term_id, 'terms');

				if($cache !== false){
					$cache_values[$term_id]	= $cache;
				}
			}

			return $cache_values;
		}	
	}

	public static function get_term($term, $taxonomy='', $output=OBJECT, $filter='raw'){
		if($term && is_numeric($term)){
			$found	= false;
			$cache	= wp_cache_get($term, 'terms', false, $found);

			if($found){
				if(is_wp_error($cache)){
					return $cache;
				}elseif(!$cache){
					return null;
				}
			}
		}

		return get_term($term, $taxonomy, $output, $filter);
	}

	public static function filter_pre_term_link($term_link, $term){
		$taxonomy	= $term->taxonomy;

		if(empty(get_taxonomy($taxonomy)->permastruct)){
			return $term_link;
		}

		return str_replace('%'.$taxonomy.'_id%', $term->term_id, $term_link);
	}

	public static function init(){
		foreach (self::get_taxonomies() as $taxonomy=>$args) {
			$object_type	= $args['object_type'];
			$taxonomy_args	= $args['args'] ?? $args;
			$taxonomy_args	= wp_parse_args($taxonomy_args, [
				'show_ui'			=> true,
				'show_in_nav_menus'	=> false,
				'show_admin_column'	=> true,
				'hierarchical'		=> true,
				'rewrite'			=> true,
				'permastruct'		=> false,
				'supports'			=> ['slug', 'description', 'parent']
			]);

			$permastruct	= $taxonomy_args['permastruct'];

			if($permastruct){
				if(empty($taxonomy_args['rewrite'])){
					$taxonomy_args['rewrite']	= true;
				}
				
				if(strpos($permastruct, '%term_id%') || strpos($permastruct, '%'.$taxonomy.'_id%')){
					$taxonomy_args['query_var']	= false;
					$taxonomy_args['supports']	= array_diff($taxonomy_args['supports'], ['slug']);
				}
			}

			if($taxonomy_args['rewrite']){
				if(is_array($taxonomy_args['rewrite'])){
					$taxonomy_args['rewrite']	= wp_parse_args($taxonomy_args['rewrite'], ['with_front'=>false, 'feed'=>false, 'hierarchical'=>false]);
				}else{
					$taxonomy_args['rewrite']	= ['with_front'=>false, 'feed'=>false, 'hierarchical'=>false];
				}

				if($permastruct && $taxonomy_args['rewrite']['hierarchical']){
					if(strpos($permastruct, '%term_id%') || strpos($permastruct, '%'.$taxonomy.'_id%')){
						$taxonomy_args['permastruct']	= $permastruct	= false;
					}
				}
			}

			register_taxonomy($taxonomy, $object_type, $taxonomy_args);

			if($permastruct){
				global $wp_rewrite;

				if(strpos($permastruct, '%term_id%') || strpos($permastruct, '%'.$taxonomy.'_id%')){
					$wp_rewrite->extra_permastructs[$taxonomy]['struct']	= str_replace('%term_id%', '%'.$taxonomy.'_id%', $permastruct);

					add_rewrite_tag('%'.$taxonomy.'_id%', '([^/]+)', 'taxonomy='.$taxonomy.'&term_id=');
					remove_rewrite_tag('%'.$taxonomy.'%');
				}elseif(strpos($permastruct, '%'.get_taxonomy($taxonomy)->rewrite['slug'].'%')){
					$wp_rewrite->extra_permastructs[$taxonomy]['struct']	= $permastruct;
				}
			}
		}	
	}
}

class WPJAM_API{
	protected static $apis	= [];

	public static function register($json, $args){
		self::$apis[$json]	= $args;
	}

	public static function get_apis(){
		return self::$apis;
	}

	public static function get_api($json){
		if(self::$apis && !empty(self::$apis[$json])){
			return self::$apis[$json];
		}else{
			return [];
		}
	}

	public static function get_filter_name($name='', $type=''){
		$filter	= str_replace('-', '_', $name);
		$filter	= str_replace('wpjam_', '', $filter);

		return 'wpjam_'.$filter.'_'.$type;
	}

	public static function validate_quota($json, $max_times=1000){
		$today	= date('Y-m-d', current_time('timestamp'));
		$times	= wp_cache_get($json.':'.$today, 'wpjam_api_times');
		$times	= $times ?: 0;

		if($times < $max_times){
			wp_cache_set($json.':'.$today, $times+1, 'wpjam_api_times', DAY_IN_SECONDS);
			return true;
		}else{
			return new WP_Error('api_exceed_quota', 'API 调用次数超限');
		}	
	}

	public static function method_allow($method, $send=true){
		if ($_SERVER['REQUEST_METHOD'] != $method) {
			$wp_error = new WP_Error('method_not_allow', '接口不支持 '.$_SERVER['REQUEST_METHOD'].' 方法，请使用 '.$method.' 方法！');
			if($send){
				self::send_json($wp_error);
			}else{
				return $wp_error;
			}
		}else{
			return true;		
		}
	}

	private static function get_post_input(){
		static $post_input;
		if(!isset($post_input)) {
			$post_input	= file_get_contents('php://input');
			// trigger_error(var_export($post_input,true));
			if(is_string($post_input)){
				$post_input	= @self::json_decode($post_input);
			}
		}

		return $post_input;
	}

	public static function get_parameter($parameter, $args=[]){
		$value		= null;
		$method		= !empty($args['method']) ? strtoupper($args['method']) : 'GET';

		if ($method == 'GET') {
			if(isset($_GET[$parameter])){
				$value = $_GET[$parameter];
			}
		} elseif ($method == 'POST') {
			if(empty($_POST)){
				$post_input	= self::get_post_input();

				if(is_array($post_input) && isset($post_input[$parameter])){
					$value = $post_input[$parameter];
				}
			}else{
				if(isset($_POST[$parameter])){
					$value = $_POST[$parameter];
				}
			}
		} else {
			if(!isset($_GET[$parameter]) && empty($_POST)){
				$post_input	= self::get_post_input();
				
				if(is_array($post_input) && isset($post_input[$parameter])){
					$value = $post_input[$parameter];
				}
			}else{
				if(isset($_REQUEST[$parameter])){
					$value = $_REQUEST[$parameter];
				}
			}
		}

		if(is_null($value) && isset($args['default'])){
			return $args['default'];
		}

		$validate_callback	= $args['validate_callback'] ?? '';

		$send	= $args['send'] ?? true;

		if($validate_callback && is_callable($validate_callback)){
			$result	= call_user_func($validate_callback, $value);

			if($result === false){
				$wp_error = new WP_Error('invalid_parameter', '非法参数：'.$parameter);

				if($send){
					self::send_json($wp_error);
				}else{
					return $wp_error;
				}
			}elseif(is_wp_error($result)){
				if($send){
					self::send_json($result);
				}else{
					return $result;
				}
			}
		}else{
			if(!empty($args['required']) && is_null($value)) {
				$wp_error = new WP_Error('missing_parameter', '缺少参数：'.$parameter);

				if($send){
					self::send_json($wp_error);
				}else{
					return $wp_error;
				}
			}

			$length	= $args['length'] ?? 0;
			$length	= intval($length);

			if($length && (mb_strlen($value) < $length)){
				$wp_error = new WP_Error('short_parameter', $parameter.' 参数长度不能少于 '.$length);

				if($send){
					self::send_json($wp_error);
				}else{
					return $wp_error;
				}
			}
		}

		$sanitize_callback	= $args['sanitize_callback'] ?? '';

		if($sanitize_callback && is_callable($sanitize_callback)){
			$value	= call_user_func($sanitize_callback, $value);
		}else{
			if(!empty($args['type']) && $args['type'] == 'int' && $value) {
				$value	= intval($value);
			}
		}
		
		return $value;
	}

	public static function get_data_parameter($parameter, $args=[]){
		$value		= null;

		if(isset($_GET[$parameter])){
			$value	= $_GET[$parameter];
		}elseif(isset($_REQUEST['data'])){
			$data		= wp_parse_args($_REQUEST['data']);
			$defaults	= !empty($_REQUEST['defaults']) ? wp_parse_args($_REQUEST['defaults']) : [];
			$data		= wpjam_array_merge($defaults, $data);

			if(isset($data[$parameter])){
				$value	= $data[$parameter];
			}
		}

		if(is_null($value) && isset($args['default'])){
			return $args['default'];
		}

		$sanitize_callback	= $args['sanitize_callback'] ?? '';

		if(is_callable($sanitize_callback)){
			$value	= call_user_func($sanitize_callback, $value);
		}

		return $value;
	}

	public static function json_encode( $data, $options=JSON_UNESCAPED_UNICODE, $depth = 512){
		return wp_json_encode($data, $options, $depth);
	}

	public static function send_json($response=[], $status_code=null){
		if(is_wp_error($response)){
			$response	= ['errcode'=>$response->get_error_code(), 'errmsg'=>$response->get_error_message()];
		}else{
			$response	= array_merge(['errcode'=>0], $response);
		}

		$result	= self::json_encode($response);

		if(!headers_sent() && !wpjam_doing_debug()){
			if (!is_null($status_code)) {
				status_header($status_code);
			}

			if(wp_is_jsonp_request()){
				@header('Content-Type: application/javascript; charset=' . get_option('blog_charset'));	
				
				$jsonp_callback	= $_GET['_jsonp'];
				
				$result	= '/**/' . $jsonp_callback . '(' . $result . ')';

			}else{	
				@header('Content-Type: application/json; charset=' . get_option('blog_charset'));
			}
		}

		echo $result;

		exit;
	}

	public static function json_decode($json, $assoc=true, $depth=512, $options=0){
		$json	= wpjam_strip_control_characters($json);

		if(empty($json)){
			return new WP_Error('empty_json', 'JSON 内容不能为空！');
		}

		$result	= json_decode($json, $assoc, $depth, $options);

		if(is_null($result)){
			$result	= json_decode(stripslashes($json), $assoc, $depth, $options);
			
			if(is_null($result)){
				if(wpjam_doing_debug()){
					print_r(json_last_error());
					print_r(json_last_error_msg());
				}
				trigger_error('json_decode_error '. json_last_error_msg()."\n".var_export($json,true));
				return new WP_Error('json_decode_error', json_last_error_msg());
			}
		}

		return $result;

		// wp 5.3 不建议使用 Services_JSON
		if(is_null($result)){
			require_once( ABSPATH . WPINC . '/class-json.php' );

			$wp_json	= new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$result		= $wp_json->decode($json); 

			if(is_null($result)){
				return new WP_Error('json_decode_error', json_last_error_msg());
			}else{
				if($assoc){
					return (array)$result;
				}else{
					return (object)$result;
				}
			}
		}else{
			return $result;
		}
	}

	public static function http_request($url, $args=[], $err_args=[]){
		$args = wp_parse_args($args, [
			'timeout'			=> 5,
			'method'			=> '',
			'body'				=> [],
			'headers'			=> [],
			'sslverify'			=> false,
			'blocking'			=> true,	// 如果不需要立刻知道结果，可以设置为 false
			'stream'			=> false,	// 如果是保存远程的文件，这里需要设置为 true
			'filename'			=> null,	// 设置保存下来文件的路径和名字
			'need_json_decode'	=> true,
			'need_json_encode'	=> false,
			// 'headers'		=> ['Accept-Encoding'=>'gzip;'],	//使用压缩传输数据
			// 'headers'		=> ['Accept-Encoding'=>''],
			// 'compress'		=> false,
			'decompress'		=> true,
		]);

		if(wpjam_doing_debug()){
			print_r($url);
			print_r($args);
		}

		$need_json_decode	= $args['need_json_decode'];
		$need_json_encode	= $args['need_json_encode'];

		if(!empty($args['method'])){
			$method			= strtoupper($args['method']);
		}else{
			$method			= $args['body'] ? 'POST' : 'GET';
		}

		unset($args['need_json_decode']);
		unset($args['need_json_encode']);
		unset($args['method']);

		if($method == 'GET'){
			$response = wp_remote_get($url, $args);
		}elseif($method == 'POST'){
			if($need_json_encode){
				if(is_array($args['body'])){
					$args['body']	= self::json_encode($args['body']);	
				}

				if(empty($args['headers']['Content-Type'])){
					$args['headers']['Content-Type']	= 'application/json';
				}
			}

			$response	= wp_remote_post($url, $args);
		}elseif($method == 'FILE'){	// 上传文件
			$args['method']				= $args['body'] ? 'POST' : 'GET';
			$args['sslcertificates']	= $args['sslcertificates'] ?? ABSPATH.WPINC.'/certificates/ca-bundle.crt';
			$args['user-agent']			= $args['user-agent'] ?? 'WordPress';

			$wp_http_curl	= new WP_Http_Curl();
			$response		= $wp_http_curl->request($url, $args);
		}elseif($method == 'HEAD'){
			if($need_json_encode && is_array($args['body'])){
				$args['body']	= self::json_encode($args['body']);
			}

			$response = wp_remote_head($url, $args);
		}else{
			if($need_json_encode && is_array($args['body'])){
				$args['body']	= self::json_encode($args['body']);
			}

			$response = wp_remote_request($url, $args);
		}

		if(is_wp_error($response)){
			trigger_error($url."\n".$response->get_error_code().' : '.$response->get_error_message()."\n".var_export($args['body'],true));
			return $response;
		}

		if(!empty($response['response']['code']) && $response['response']['code'] != 200){
			return new WP_Error($response['response']['code'], '远程服务器错误：'.$response['response']['code'].' - '.$response['response']['message']);
		}

		$headers	= $response['headers'];
		$response	= $response['body'];

		if($need_json_decode || isset($headers['content-type']) && strpos($headers['content-type'], '/json')){
			if($args['stream']){
				$response	= file_get_contents($args['filename']);
			}

			if(empty($response)){
				trigger_error($response);
			}else{
				$response	= self::json_decode($response);

				if(is_wp_error($response)){
					return $response;
				}
			}
		}
		
		$err_args	= wp_parse_args($err_args,  [
			'errcode'	=>'errcode',
			'errmsg'	=>'errmsg',
			'detail'	=>'detail',
			'success'	=>'0',
		]);

		if(isset($response[$err_args['errcode']]) && $response[$err_args['errcode']] != $err_args['success']){
			$errcode	= $response[$err_args['errcode']];
			$errmsg		= $response[$err_args['errmsg']] ?? '';

			if(isset($response[$err_args['detail']])){
				$detail	= $response[$err_args['detail']];

				trigger_error($url."\n".$errcode.' : '.$errmsg."\n".var_export($detail,true)."\n".var_export($args['body'],true));
				return new WP_Error($errcode, $errmsg, $detail);
			}else{

				trigger_error($url."\n".$errcode.' : '.$errmsg."\n".var_export($args['body'],true));
				return new WP_Error($errcode, $errmsg);
			}	
		}

		if(wpjam_doing_debug()){
			echo $url;
			print_r($response);
		}

		return $response;
	}
}

class WPJAM_Route extends WPJAM_API{
	public static function get_current_user(){
		return apply_filters('wpjam_current_user', null);
	}

	public static function get_json(){
		if(self::is_json_request()){
			global $wp;
			
			if(isset($wp->query_vars['module']) && $wp->query_vars['module'] == 'json'){
				$action	= $wp->query_vars['action'] ?? '';

				return str_replace(['mag.','/'], ['','.'], $action);
			}
		}

		return '';
	}

	public static function is_json_request(){
		if(get_option('permalink_structure')){
			if(preg_match("/\/api\/(.*)\.json/", $_SERVER['REQUEST_URI'])){ 
				return true;
			}
		}else{
			if(isset($_GET['module']) && $_GET['module'] == 'json'){
				return true;
			}
		}
			
		return false;
	}

	public static function send_origin_headers(){
		header('X-Content-Type-Options: nosniff');

		$origin = get_http_origin();

		if ( $origin ) {
			// Requests from file:// and data: URLs send "Origin: null"
			if ( 'null' !== $origin ) {
				$origin = esc_url_raw( $origin );
			}

			@header( 'Access-Control-Allow-Origin: ' . $origin );
			@header( 'Access-Control-Allow-Methods: GET, POST' );
			@header( 'Access-Control-Allow-Credentials: true' );
			@header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
			@header( 'Vary: Origin' );

			if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
				exit;
			}
		}
		
		if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
			status_header( 403 );
			exit;
		}
	}

	public static function parse_query_vars($wp){
		$query_vars	= $wp->query_vars;

		$tax_query	= [];

		if(!empty($query_vars['tag_id']) && $query_vars['tag_id'] == -1){
			$tax_query[]	= [
				'taxonomy'	=> 'post_tag',
				'field'		=> 'term_id',
				'operator'	=> 'NOT EXISTS'
			];

			unset($query_vars['tag_id']);
		}

		if(!empty($query_vars['cat']) && $query_vars['cat'] == -1){
			$tax_query[]	= [
				'taxonomy'	=> 'category',
				'field'		=> 'term_id',
				'operator'	=> 'NOT EXISTS'
			];

			unset($query_vars['cat']);
		}

		if($taxonomy_objs = get_taxonomies(['_builtin'=>false], 'objects')){
			foreach ($taxonomy_objs as $taxonomy => $taxonomy_obj){
				$tax_key	= $taxonomy.'_id';

				if(empty($query_vars[$tax_key])){
					continue;
				}

				$current_term_id	= $query_vars[$tax_key];
				unset($query_vars[$tax_key]);

				if($current_term_id == -1){
					$tax_query[]	= [
						'taxonomy'	=> $taxonomy,
						'field'		=> 'term_id',
						'operator'	=> 'NOT EXISTS'
					];
				}else{
					$tax_query[]	= [
						'taxonomy'	=> $taxonomy,
						'terms'		=> [$current_term_id],
						'field'		=> 'term_id',
					];
				}
			}
		}

		if(!empty($query_vars['taxonomy']) && empty($query_vars['term']) && !empty($query_vars['term_id'])){
			if(is_numeric($query_vars['term_id'])){
				$tax_query[]	= [
					'taxonomy'	=> $query_vars['taxonomy'],
					'terms'		=> [$query_vars['term_id']],
					'field'		=> 'term_id',
				];
			}else{
				$wp->set_query_var('term', $query_vars['term_id']);
			}
		}

		if($tax_query){
			if(!empty($query_vars['tax_query'])){
				$query_vars['tax_query'][]	= $tax_query;
			}else{
				$query_vars['tax_query']	= $tax_query;
			}

			$wp->set_query_var('tax_query', $tax_query);
		}

		$date_query	= $query_vars['date_query'] ?? [];

		if(!empty($query_vars['cursor'])){
			$date_query[]	= ['before' => get_date_from_gmt(date('Y-m-d H:i:s', $query_vars['cursor']))];
		}

		if(!empty($query_vars['since'])){
			$date_query[]	= ['after' => get_date_from_gmt(date('Y-m-d H:i:s', $query_vars['since']))];
		}

		if($date_query){
			$wp->set_query_var('date_query', $date_query);
		}
	}

	public static function json_request($action){
		if(!wpjam_doing_debug()){ 
			if(wp_is_jsonp_request()){
				@header('Content-Type: application/javascript; charset='.get_option('blog_charset'));
			}else{
				@header('Content-Type: application/json; charset='.get_option('blog_charset'));	
			}
		}
			
		if(strpos($action, 'mag.') !== 0){
			return;
		}
				
		$json	= str_replace(['mag.','/'], ['','.'], $action);

		do_action('wpjam_api_template_redirect', $json);

		$api_setting	= self::get_api($json);

		if(!$api_setting){
			self::send_json([
				'errcode'	=> 'api_not_defined',
				'errmsg'	=> '接口未定义！',
			]);
		}

		if(!empty($api_setting['quota'])){
			$result	= self::validate_quota($json, $api_setting['quota']);

			if(is_wp_error($result)){
				self::send_json($result);
			}
		}
		
		$wpjam_user	= self::get_current_user();

		if(is_wp_error($wpjam_user)){
			if(!empty($api_setting['auth'])){
				self::send_json($wpjam_user);
			}else{
				$wpjam_user	= null;
			}
		}elseif(is_null($wpjam_user)){
			if(!empty($api_setting['auth'])){
				self::send_json([
					'errcode'	=>'bad_authentication', 
					'errmsg'	=>'无权限'
				]);
			}
		}

		$response	= ['errcode'=>0];

		if(empty($api_setting['modules'])){
			$response	= $response + $api_setting;
		}else{
			$response['current_user']	= $wpjam_user;
			$response['page_title']		= $api_setting['page_title'] ?? '';
			$response['share_title']	= $api_setting['share_title'] ?? '';
			$response['share_image']	= !empty($api_setting['share_image']) ? wpjam_get_thumbnail($api_setting['share_image'], '500x400') : '';

			foreach ($api_setting['modules'] as $module){
				if(!$module['type'] || !$module['args']){
					continue;
				}
				
				if(is_array($module['args'])){
					$args = $module['args'];
				}else{
					$args = wpjam_parse_shortcode_attr(stripslashes_deep($module['args']), 'module');
				}

				$module_type	= $module['type'];
				$module_action	= $args['action'] ?? '';
				$output			= $args['output'] ?? '';

				if(in_array($module_type, ['post_type', 'taxonomy', 'media', 'setting', 'other'])){
					$module_template	= WPJAM_BASIC_PLUGIN_DIR.'api/'.$module_type.'.php';
				}else{
					$module_template	= '';
				}

				$module_template	= apply_filters('wpjam_api_template_include', $module_template, $module_type, $module);

				if($module_template && is_file($module_template)){
					include $module_template;
				}
			}
		}

		$response = apply_filters('wpjam_json', $response, $api_setting, $json);

		self::send_json($response);
	}

	public static function is_module($module='', $action=''){
		$current_module	= get_query_var('module');
		$current_action	= get_query_var('action');

		// 没设置 module
		if(!$current_module){
			return false;
		}

		if($module && $action){
			return $module == $current_module && $action == $current_action;
		}elseif($module){
			return $module == $current_module;
		}else{
			return true;
		}
	}

	public static function on_parse_request($wp){
		$module = $wp->query_vars['module'] ?? '';
		$action = $wp->query_vars['action'] ?? '';
	
		if($module == 'json' && strpos($action, 'mag.') === 0){
			return;
		}

		self::parse_query_vars($wp);
	}

	public static function on_send_headers($wp){
		$module = $wp->query_vars['module'] ?? '';

		if($module){
			remove_action('template_redirect', 'redirect_canonical');

			$action = $wp->query_vars['action'] ?? '';

			if($module == 'json'){
				self::send_origin_headers();
				self::json_request($action);
			}
			
			do_action('wpjam_module', $module, $action);
		}
	}

	public static function filter_current_user($user_id){
		if(empty($user_id)){
			$wpjam_user	= self::get_current_user();

			if($wpjam_user && !is_wp_error($wpjam_user) && !empty($wpjam_user['user_id'])){
				return $wpjam_user['user_id'];
			}
		}

		return $user_id;
	}

	public static function filter_current_commenter($commenter){
		if(empty($commenter['comment_author_email'])){
			$wpjam_user	= self::get_current_user();

			if(is_wp_error($wpjam_user)){
				return $wpjam_user;
			}elseif(empty($wpjam_user) || empty($wpjam_user['user_email'])){
				return new WP_Error('bad_authentication', '无权限');
			}else{
				$commenter['comment_author_email']	= $wpjam_user['user_email'];
				$commenter['comment_author']		= $wpjam_user['nickname'];
			}	
		}

		return $commenter;
	}

	public static function filter_template_include($template){
		$module	= get_query_var('module');

		if($module){
			$action	= get_query_var('action');
			$action = ($action == 'new' || $action == 'add')?'edit':$action;

			if($action){
				$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/'.$action.'.php';
			}else{
				$wpjam_template = STYLESHEETPATH.'/template/'.$module.'/index.php';
			}

			$wpjam_template		= apply_filters('wpjam_template', $wpjam_template, $module, $action);

			if(is_file($wpjam_template)){
				return $wpjam_template;
			}else{
				wp_die('路由错误！');
			}
		}

		return $template;
	}

	public static function init(){
		global $wp, $wp_rewrite;

		$wp->add_query_var('term_id');
		$wp->add_query_var('module');
		$wp->add_query_var('action');
		
		add_rewrite_rule($wp_rewrite->root.'api/([^/]+)/(.*?)\.json?$',	'index.php?module=json&action=mag.$matches[1].$matches[2]', 'top');
		add_rewrite_rule($wp_rewrite->root.'api/([^/]+)\.json?$',		'index.php?module=json&action=$matches[1]', 'top');
	}
}