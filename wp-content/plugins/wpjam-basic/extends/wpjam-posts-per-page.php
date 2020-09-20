<?php
/*
Plugin Name: 文章数量
Plugin URI: http://blog.wpjam.com/project/wpjam-posts-per-page/
Description: 设置不同页面不同的文章列表数量，不同的分类不同文章列表数量。
Version: 1.0
*/
function wpjam_get_posts_per_page($setting){
	return wpjam_get_setting('wpjam-posts-per-page', $setting);
}

function wpjam_get_post_types_per_page($setting){
	return wpjam_get_setting('wpjam-posts-per-page', $setting.'_post_types');
}

if(is_admin()){
	add_action('wpjam_builtin_page_load', function ($screen_base, $current_screen){
		$taxonomy	= $current_screen->taxonomy;

		if(!in_array($screen_base, ['edit-tags', 'term']) || !is_taxonomy_hierarchical($taxonomy) || !wpjam_get_posts_per_page($taxonomy.'_individual')){
		}

		add_action('wpjam_'.$taxonomy.'_terms_actions', function($actions, $taxonomy){
			return $actions+['posts_per_page'=>['title'=>'文章数量',	'page_title'=>'设置文章数量',	'submit_text'=>'设置',	'tb_width'=>400]];
		}, 9, 2);

		add_filter($taxonomy.'_row_actions', function($actions, $term){
			$posts_per_page	= get_term_meta($term->term_id, 'posts_per_page', true);
			$posts_per_page	= $posts_per_page ? '（'.$posts_per_page.'）' : '';
			
			$actions['posts_per_page']	= str_replace('>文章数量<', '>文章数量'.$posts_per_page.'<', $actions['posts_per_page']);
			return $actions;
		},10,2);

		wpjam_register_term_option('posts_per_page', ['title'=>'文章数量',	'type'=>'number',	'class'=>'',	'description'=>'页面显示文章数量',	'action'=>'edit']);
	}, 10, 2);
}else{
	add_action('pre_get_posts',  function($wp_query) {
		if(!$wp_query->is_main_query()){
			return;
		}

		if(is_home() || is_front_page()){
			if($number	= wpjam_get_posts_per_page('home')){
				$wp_query->set('posts_per_page', $number);
			}

			if(!isset($wp_query->query['post_type'])){
				if($post_types	= wpjam_get_post_types_per_page('home')){
					$wp_query->set('post_type', $post_types);
				}
			}
		}elseif(is_feed()){
			if(!isset($wp_query->query['post_type'])){
				if($post_types	= wpjam_get_post_types_per_page('feed')){
					$wp_query->set('post_type', $post_types);
				}
			}
		}elseif(is_author()){
			if($number	= wpjam_get_posts_per_page('author')){
				$wp_query->set('posts_per_page', $number);
			}

			if(!isset($wp_query->query['post_type'])){
				if($post_types	= wpjam_get_post_types_per_page('author')){
					$wp_query->set('post_type', $post_types);
				}
			}
		}elseif(is_tax() || is_category() || is_tag()){
			$term	= $wp_query->get_queried_object();

			if(empty($term)){
				return;
			}

			$taxonomy	= $term->taxonomy;

			$number		= wpjam_get_posts_per_page($taxonomy);
			$individual	= wpjam_get_posts_per_page($taxonomy.'_individual');

			if($individual && metadata_exists('term', $term->term_id, 'posts_per_page')){
				$number	= get_term_meta($term->term_id, 'posts_per_page', true);
			}

			if($number){
				$wp_query->set('posts_per_page', $number);	
			}

			if((is_category() || is_tag()) && !isset($wp_query->query['post_type'])){
				
				$post_types	= get_taxonomy($term->taxonomy)->object_type;
				$post_types	= array_intersect($post_types, get_post_types(['public'=>true]));

				if (!$post_types){
					return;
				} elseif (count( $post_types ) == 1) {
					$post_types	= $post_types[0];
				}

				$wp_query->set('post_type', $post_types);
			}
		}elseif(is_post_type_archive()){
			$pt_object	= $wp_query->get_queried_object();

			if($number	= wpjam_get_posts_per_page($pt_object->name)){
				$wp_query->set('posts_per_page', $number);
			}
		}elseif(is_search()){
			if($number	= wpjam_get_posts_per_page('search')){
				$wp_query->set('posts_per_archive_page', $number);
			}
		}elseif(is_archive()){
			if($number	= wpjam_get_posts_per_page('archive')){
				$wp_query->set('posts_per_archive_page', $number);
			}

			if(!isset($wp_query->query['post_type'])){
				$wp_query->set('post_type', 'any');
			}
		}
	});
}
