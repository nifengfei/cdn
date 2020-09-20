<?php
/*
Plugin Name: 简单 SEO
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 设置简单快捷，功能强大的 WordPress SEO 功能。
Version: 1.0
*/
function wpjam_seo_get_setting($setting_name){
	if(wpjam_get_option('wpjam-seo')){
		return wpjam_get_setting('wpjam-seo', $setting_name);
	}else{
		return wpjam_basic_get_setting('seo_'.$setting_name);
	}
}

add_filter('robots_txt', function ($output, $public){
	if ( '0' == $public ) {
		return "Disallow: /\n";
	} else {
		return wpjam_seo_get_setting('robots');
	}
},10,2);

add_action('init',function(){
	global $wp_rewrite;
	add_rewrite_rule($wp_rewrite->root.'sitemap\.xml?$', 'index.php?module=sitemap', 'top');
	add_rewrite_rule($wp_rewrite->root.'sitemap-(.*?)\.xml?$', 'index.php?module=sitemap&action=$matches[1]', 'top');
});

if(!is_admin()){
	remove_action('wp_head', 'noindex', 1);

	add_action('wp_head', function (){
		global $paged;

		$meta_keywords	= $meta_description	= '';

		$seo_individual	= wpjam_seo_get_setting('individual');

		if(is_singular()){
			$post_id = get_the_ID();

			if($seo_individual){
				$seo_post_types	= wpjam_seo_get_setting('post_types') ?? ['post'];

				if($seo_post_types && in_array(get_post_type(), $seo_post_types)){
					if($seo_description = get_post_meta($post_id, 'seo_description', true)){
						$meta_description = $seo_description;
					}

					if($seo_keywords = get_post_meta($post_id, 'seo_keywords', true)){
						$meta_keywords	= $seo_keywords;
					}
				}	
			}

			if(empty($meta_description)){
				$meta_description	= get_the_excerpt();
			}

			if(empty($meta_keywords)){
				if($tags = get_the_tags($post_id)){
					$meta_keywords = implode(',', wp_list_pluck($tags, 'name'));
				}
			}
		}elseif($paged<2){
			if((is_home() || is_front_page()) && !wpjam_is_module()){
				$meta_description	= wpjam_seo_get_setting('home_description') ?: '';
				$meta_keywords		= wpjam_seo_get_setting('home_keywords') ?: '';
			}elseif(is_tag() || is_category() || is_tax()){
				if($seo_individual){
					$seo_taxonomies	= wpjam_seo_get_setting('taxonomies') ?? ['category'];

					if($seo_taxonomies && in_array(get_queried_object()->taxonomy, $seo_taxonomies)){
						$term_id	= get_queried_object_id();

						if($seo_description	= get_term_meta($term_id, 'seo_description', true)){
							$meta_description = $seo_description;
						}

						if($seo_keywords = get_term_meta($term_id, 'seo_keywords', true)){
							$meta_keywords	= $seo_keywords;
						}
					}
				}

				if(empty($meta_description) && term_description()){
					$meta_description	= term_description();
				}
			}elseif(is_post_type_archive()){
				// $post_type_obj = get_queried_object();
				
				// if(!$meta_description = wpjam_seo_get_setting($post_type->name.'_description')){
				// 	$meta_description = $post_type->description;
				// }
				// $meta_keywords = wpjam_seo_get_setting($post_type->name.'_keywords');
		    }
		}

		if($meta_description){
			$meta_description	= addslashes_gpc(wpjam_get_plain_text($meta_description));
			echo "<meta name='description' content='{$meta_description}' />\n";
		}

		if($meta_keywords){
			$meta_keywords	= addslashes_gpc(wpjam_get_plain_text($meta_keywords));
			echo "<meta name='keywords' content='{$meta_keywords}' />\n";
		}
	});

	add_filter('pre_get_document_title', function ($title){
		global $paged;

		$seo_individual	= wpjam_seo_get_setting('individual');
		
		if(is_singular()){
			if($seo_individual){
				$seo_post_types	= wpjam_seo_get_setting('post_types') ?? ['post'];

				if($seo_post_types && in_array(get_post_type(), $seo_post_types)){
					if($seo_title = get_post_meta(get_the_ID(), 'seo_title', true)){
						return $seo_title;
					}
				}
			}
		}elseif($paged<2){
			if((is_home() || is_front_page()) && !wpjam_is_module()){
				if($seo_title = wpjam_seo_get_setting('home_title')){
					return $seo_title;
				}
			}elseif(is_tag() || is_category() || is_tax()){
				if($seo_individual){
					$seo_taxonomies	= wpjam_seo_get_setting('taxonomies') ?? ['category'];

					if($seo_taxonomies && in_array(get_queried_object()->taxonomy, $seo_taxonomies)){
						if($seo_title	= get_term_meta(get_queried_object_id(), 'seo_title', true)){
							return $seo_title;
						}
					}
				}
			}elseif(is_post_type_archive()){
				// $post_type = get_queried_object();
				// if(wpjam_seo_get_setting($post_type->name.'_title')){
				// 	return wpjam_seo_get_setting($post_type->name.'_title');
				// }
			}
		}
		return $title;
	},99);

	add_filter('wpjam_template', function ($wpjam_template, $module, $action){
		if($module == 'sitemap'){
			return WPJAM_BASIC_PLUGIN_DIR.'template/sitemap.php';
		}
		return $wpjam_template;
	}, 10, 3);
}else{
	add_filter('wpjam_basic_sub_pages',function($subs){
		$subs['wpjam-seo']	= [
			'menu_title'	=>'SEO设置',
			'page_title'	=>'简单SEO',
			'function'		=>'option'
		];

		return $subs;
	});

	if(wpjam_seo_get_setting('individual')){
		add_action('wpjam_builtin_page_load', function ($screen_base, $current_screen){
			if($screen_base == 'edit'){
				$seo_post_types	= wpjam_seo_get_setting('post_types') ?? ['post'];

				if($seo_post_types  && in_array($current_screen->post_type, $seo_post_types)){
					add_action('wpjam_'.$current_screen->post_type.'_posts_actions', function($actions){
						return $actions + ['seo'=>['title'=>'SEO设置', 'page_title'=>'SEO设置',	'submit_text'=>'设置']];
					});
				}
			}elseif($screen_base == 'post'){
				$seo_post_types	= wpjam_seo_get_setting('post_types') ?? ['post'];

				if($seo_post_types && in_array($current_screen->post_type, $seo_post_types)){
					add_filter('wpjam_post_options',function($post_options){
						$seo_post_options	= [
							'title'		=> 'SEO设置',
							'fields'	=> [
								'seo_title'			=> ['title'=>'标题', 	'type'=>'text',		'class'=>'large-text',		'placeholder'=>'不填则使用文章标题'],
								'seo_description'	=> ['title'=>'描述', 	'type'=>'textarea'],
								'seo_keywords'		=> ['title'=>'关键字',	'type'=>'text',		'class'=>'large-text']
							]
						];

						if(function_exists('use_block_editor_for_post_type') && use_block_editor_for_post_type(get_current_screen()->post_type)){
							$seo_post_options['context']	= 'side';
						}

						return $post_options + ['wpjam-seo'=>$seo_post_options];
					});
				}		
			}elseif($screen_base == 'edit-tags' || $screen_base == 'term'){
				$seo_taxonomies	= wpjam_seo_get_setting('taxonomies') ?? ['category'];

				if($seo_taxonomies && in_array($current_screen->taxonomy, $seo_taxonomies)){
					add_action('wpjam_'.$current_screen->taxonomy.'_terms_actions', function($actions, $taxonomy){
						return $actions + ['seo'=>['title'=>'SEO设置', 'page_title'=>'SEO设置',	'submit_text'=>'设置']];
					}, 10, 2);

					add_filter('wpjam_term_options', function($term_options){
						return $term_options	+ [
							'seo_title' 		=> ['title'=>'SEO 标题',		'type'=>'text',		'action'=>'edit'],
							'seo_description'	=> ['title'=>'SEO 描述',		'type'=>'textarea',	'action'=>'edit'],
							'seo_keywords'		=> ['title'=>'SEO 关键字',	'type'=>'text',		'action'=>'edit'],
						];
					});
				}
			}
		}, 10, 2);
	}

	add_action('wpjam_plugin_page_load', function($plugin_page){
		if($plugin_page != 'wpjam-seo'){
			return;
		}

		if(!wpjam_get_option('wpjam-seo')){
			$seo_value	= [];

			foreach (['seo_individual', 'seo_post_types', 'seo_taxonomies', 'seo_robots', 'seo_home_title', 'seo_home_description', 'seo_home_keywords'] as $setting_name){
				if($setting_value	= wpjam_basic_get_setting($setting_name)){
					$seo_value[ltrim($setting_name, 'seo_')]	= $setting_value;
				}

				wpjam_basic_delete_setting($setting_name);
			}

			if($seo_value){
				update_option('wpjam-seo', $seo_value);
			}
		}

		add_filter('wpjam_seo_setting', function (){
			$site_url = parse_url( site_url() );
			$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';

			if(file_exists(ABSPATH.'robots.txt')){
				$robots_type	= 'view';
				$robots_value	= '博客的根目录下已经有 robots.txt 文件。<br />请直接编辑或者删除之后在后台自定义。';
			}else{
				$robots_type	= 'textarea';
				$robots_value	= "User-agent: *
		Disallow: /wp-admin/
		Disallow: /wp-includes/
		Disallow: /cgi-bin/
		Disallow: $path/wp-content/plugins/
		Disallow: $path/wp-content/themes/
		Disallow: $path/wp-content/cache/
		Disallow: $path/author/
		Disallow: $path/trackback/
		Disallow: $path/feed/
		Disallow: $path/comments/
		Disallow: $path/search/";
			}
			
			if(file_exists(ABSPATH.'sitemap.xml')){
				$sitemap_value	= '博客的根目录下已经有 sitemap.xml 文件。<br />删除之后才能使用插件自动生成的 sitemap.xml。';
			}else{
				$sitemap_value	= '<table>
					<tr><td style="padding:0 10px 8px 0;">首页/分类/标签：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap.xml').'" target="_blank">'.home_url('/sitemap.xml').'</a></td></tr>
					<tr><td style="padding:0 10px 8px 0;">前1000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-1.xml').'" target="_blank">'.home_url('/sitemap-1.xml').'</a></td></tr>
					<tr><td style="padding:0 10px 8px 0;">1000-2000篇文章：</td><td style="padding:0 10px 8px 0;"><a href="'.home_url('/sitemap-2.xml').'" target="_blank">'.home_url('/sitemap-2.xml').'</a></td></tr>
					<tr><td style="padding:0 10px 8px 0;" colspan=2>以此类推...</a></td></tr>
				</table>';
			}

			$post_type_options	= wp_list_pluck(get_post_types(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');
			$taxonomy_options	= wp_list_pluck(get_taxonomies(['show_ui'=>true,'public'=>true], 'objects'), 'label', 'name');

			unset($post_type_options['attachment']);

			$individual_options	= [0=>'文章和分类页自动获取摘要和关键字',1=>'文章和分类页单独的 SEO TDK 设置。'];
			$auto_view			= '文章摘要作为页面的 Meta Description，文章的标签作为页面的 Meta Keywords。<br />
			分类和标签的描述作为页面的 Meta Description，页面没有 Meta Keywords。';

			$sections = [ 
				'setting'	=> [
					'title'		=>'SEO设置',	
					'fields'	=>[
						'individual'		=> ['title'=>'SEO设置',		'type'=>'select', 	'options'=>$individual_options],
						'auto'				=> ['title'=>'自动获取规则',	'type'=>'view', 	'show_if'=>['key'=>'individual', 'value'=>'0'],	'value'=>$auto_view],
						'individual_set'	=> ['title'=>'单独设置支持',	'type'=>'fieldset',	'show_if'=>['key'=>'individual', 'value'=>'1'],	'fields'=>[
							'post_types'	=> ['title'=>'文章类型','type'=>'checkbox',	'options'=>$post_type_options,	'value'=>['post']],
							'taxonomies'	=> ['title'=>'分类模式','type'=>'checkbox',	'options'=>$taxonomy_options,	'value'=>['category']],
						]],	
						'robots'		=> ['title'=>'robots.txt',	'type'=>$robots_type,	'class'=>'',	'rows'=>10,	'value'=>$robots_value],
						'sitemap'		=> ['title'=>'Sitemap',		'type'	=>'view',	'value'=>$sitemap_value]
					]
				],
				'home'		=> [
					'title'		=>'首页设置',	
					'fields'	=>[
						'home_title'		=> ['title'=>'SEO 标题',		'type'=>'text'],
						'home_description'	=> ['title'=>'SEO 描述',		'type'=>'textarea', 'class'=>''],
						'home_keywords'		=> ['title'=>'SEO 关键字',	'type'=>'text' ],
					]
				],
			];

			$summary	= '简单 SEO 扩展让你最简单最快捷的方式设置站点的 SEO，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-seo/" target="_blank">简单SEO扩展</a>。';

			// if(!is_multisite() || (is_multisite() && !is_network_admin())){
			// 	if($post_types = get_post_types(['public'=> true, 'has_archive'=>true],'objects')){
			// 		foreach ($post_types as $post_type) {
			// 			$post_type_object = get_post_type_object($post_type);
			// 			// if(!empty($post_type_object->seo_meta_box) || $post_type == 'post'){
			// 				$post_type_fields = [
			// 					$post_type->name.'_title'		=> ['title'=>$post_type->label.' SEO 标题',		'type'=>'text'],
			// 					$post_type->name.'_description'	=> ['title'=>$post_type->label.' SEO 描述',		'type'=>'textarea', 'class'=>''],
			// 					$post_type->name.'_keywords'		=> ['title'=>$post_type->label.' SEO Keywords',	'type'=>'text'],
			// 				];

			// 				$sections[$post_type->name.'-seo']	= ['title'=>$post_type->label, 'fields'=>$post_type_fields];
			// 			// }
			// 		}
			// 	}
			// }
			
			return compact('sections', 'summary');
		});
	});

	add_action('blog_privacy_selector', function(){
		?>
		<style type="text/css">tr.option-site-visibility{display: none;}</style>
		<?php
	});
}
