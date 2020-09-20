<?php
/*
Plugin Name: 百度站长
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: 支持主动，被动，自动以及批量方式提交链接到百度站长。
Version: 1.0
*/
function wpjam_notify_baidu_zz($urls, $args=[]){
	$query_args	= [];

	$query_args['site']		= wpjam_get_setting('baidu-zz', 'site');
	$query_args['token']	= wpjam_get_setting('baidu-zz', 'token');

	if($query_args['site'] && $query_args['token']){
		$update	= $args['update'] ?? false;
		$type	= $args['type'] ?? '';
		
		if(empty($type) && wpjam_get_setting('baidu-zz', 'mip')){
			$type	= 'mip';
		}

		if($type){
			$query_args['type']	= $type;
		}

		if($update){
			$baidu_zz_api_url	= add_query_arg($query_args, 'http://data.zz.baidu.com/update');
		}else{
			$baidu_zz_api_url	= add_query_arg($query_args, 'http://data.zz.baidu.com/urls');
		}

		$response	= wp_remote_post($baidu_zz_api_url, array(
			'headers'	=> ['Accept-Encoding'=>'','Content-Type'=>'text/plain'],
			'sslverify'	=> false,
			'blocking'	=> false,
			'body'		=> $urls
		));
	}
}

add_action('publish_future_post', function($post_id){
	$urls	= apply_filters('baiduz_zz_post_link', get_permalink($post_id))."\n";	

	wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
	wpjam_notify_baidu_zz($urls);
},11);

if(!is_admin()){
	add_action('wp_enqueue_scripts', function(){
		if(wpjam_get_setting('baidu-zz', 'no_js')){
			return;
		}

		if(is_404() || is_preview()){
			return;
		}elseif(is_singular() && get_post_status() != 'publish'){
			return;
		}

		if(is_ssl()){
			wp_enqueue_script('baidu_zz_push', 'https://zz.bdstatic.com/linksubmit/push.js', '', '', true);
		}else{
			wp_enqueue_script('baidu_zz_push', 'http://push.zhanzhang.baidu.com/push.js', '', '', true);
		}
	});
}else{
	wpjam_add_basic_sub_page('baidu-zz', [
		'menu_title'	=>'百度站长',
		'function'		=>'tab',
		'tabs'			=>[
			'baidu-zz'	=>['title'=>'百度站长',	'function'=>'option',	'option_name'=>'baidu-zz'],
			'batch'		=>['title'=>'批量提交',	'function'=>'wpjam_baidu_zz_batch_page'],
		],
		'summary'		=>'百度推送扩展由 WordPress 果酱和 <a href="https://www.baidufree.com" target="_blank">纵横SEO</a> 联合推出， 实现提交链接到百度站长，让你的博客的文章能够更快被百度收录，详细介绍请点击：<a href="https://blog.wpjam.com/m/301-redirects/" target="_blank">百度站长扩展</a>。'
	]);

	add_action('wpjam_plugin_page_load', function($plugin_page, $current_tab){
		if($plugin_page != 'baidu-zz' || !empty($current_tab)){
			return;
		}

		add_filter('wpjam_baidu_zz_setting', function(){
			return	[
				'title'		=>'', 
				'fields'	=>[
					'site'	=>['title'=>'站点 (site)',	'type'=>'text',	'class'=>'all-options'],
					'token'	=>['title'=>'密钥 (token)',	'type'=>'password'],
					'mip'	=>['title'=>'MIP',			'type'=>'checkbox', 'description'=>'博客已支持MIP'],
					'no_js'	=>['title'=>'不加载推送JS',	'type'=>'checkbox', 'description'=>'插件已支持主动推送，不加载百度推送JS'],
				]	
			];
		});

		add_action('wpjam_page_action', function($action){
			if($action == 'submit'){
				$offset	= wpjam_get_data_parameter('n',		['default'=>0, 'sanitize_callback'=>'intval']);
				$type	= wpjam_get_data_parameter('type',	['default'=>'post']);

				if($type=='post'){
					$_query	= new WP_Query([
						'post_type'			=>'any',
						'post_status'		=>'publish',
						'posts_per_page'	=>100,
						'offset'			=>$offset	
					]);

					if($_query->have_posts()){
						$count	= count($_query->posts);
						$number	= $offset+$count;	

						$urls	= '';
						while($_query->have_posts()){
							$_query->the_post();

							if(wp_cache_get(get_the_ID(), 'wpjam_baidu_zz_notified') === false){
								wp_cache_set(get_the_ID(), true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);
								$urls	.= apply_filters('baiduz_zz_post_link', get_permalink())."\n";
							}
						}

						wpjam_notify_baidu_zz($urls);

						wpjam_send_json(['number'=>$number,	'total'=>$_query->found_posts, 'type'=>$type, 'errmsg'=>'批量提交中，请勿关闭浏览器，已提交了'.$number.'篇文章。']);
					}else{
						wpjam_send_json(['number'=>0, 'type'=>'next', 'errmsg'=>'所有文章提交完成。']);
					}	
				}else{
					if($type == 'next'){
						wpjam_send_json();
					}else{
						do_action('wpjam_baidu_zz_batch_submit', $type, $offset);
						wpjam_send_json();
					}
				}
			}
		});

		function wpjam_baidu_zz_batch_page(){
			echo '<p>使用百度站长更新内容接口和移动专区周级收录接口批量将博客中的所有内容都提交给百度搜索资源平台。</p>';

			$types	= apply_filters('wpjam_baidu_zz_batch_submit_types', ['post']);

			$fields	= [
				'n'		=> ['title'=>'',	'type'=>'hidden',	'value'=>0],
				'type'	=> ['title'=>'',	'type'=>'hidden',	'value'=>'post']
			];

			wpjam_ajax_form([
				'fields'		=> $fields, 
				'action'		=> 'submit', 
				'submit_text'	=> '批量提交'
			]);
		}

		add_action('admin_head', function(){
			?>

			<script type="text/javascript">
			jQuery(function($){
				var types = <?php echo wpjam_json_encode($types); ?>;
				$('body').on('page_action_success', function(e, response){
					var action	= response.page_action;

					if(action == 'submit'){
						if(response.errmsg){
							var response_type = response.type;

							if(response_type == 'next'){
								var current_type 	= $('#type').val();
								var type_index		= types.indexOf(current_type);
								if(type_index+1 < types.length){
									response_type = types[type_index+1];
								}
							}

							$('#n').val(response.number);
							$('#type').val(response_type);

							setTimeout(function(){
								$('#wpjam_form').submit();
							}, 400);
						}else{
							$('#n').val(0);
							$('#type').val('post');
						}		
					}
				});
			});
			</script>
			<?php
		});
	}, 10, 2);

	add_action('wpjam_builtin_page_load', function($screen_base, $current_screen){
		if($screen_base != 'post' || !is_post_type_viewable($current_screen->post_type)){
			return;
		}

		add_action('save_post', function($post_id, $post, $update){
			if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)){
				return;
			}

			if(!$update && $post->post_status == 'publish'){
				$post_link	= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id);

				$args	= [];
				
				if(!empty($_POST['baidu_zz_daily'])){
					$args['type']	= 'daily';
				}
				
				wpjam_notify_baidu_zz($post_link, $args);
			}
		}, 10, 3);

		add_action('post_updated', function($post_id, $post_after, $post_before){
			if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)){
				return;
			}

			if($post_after->post_status == 'publish'){
				
				$baidu_zz_daily	= $_POST['baidu_zz_daily'] ?? false;

				if($baidu_zz_daily || wp_cache_get($post_id, 'wpjam_baidu_zz_notified') === false){
					wp_cache_set($post_id, true, 'wpjam_baidu_zz_notified', HOUR_IN_SECONDS);

					$post_link	= apply_filters('baiduz_zz_post_link', get_permalink($post_id), $post_id);

					$args	= [];
					
					if($baidu_zz_daily){
						$args['type']	= 'daily';
					}

					wpjam_notify_baidu_zz($post_link, $args);
				}
			}
		}, 10, 3);

		add_action('post_submitbox_misc_actions', function (){ ?>
			<div class="misc-pub-section" id="baidu_zz_section">
				<input type="checkbox" name="baidu_zz_daily" id="baidu_zz" value="1">
				<label for="baidu_zz_daily">提交给百度站长快速收录</label>
			</div>
		<?php },11);

		add_action('admin_enqueue_scripts', function(){
			wp_add_inline_style('wpjam-style', '#post-body #baidu_zz_section:before {content: "\f103"; color:#82878c; font: normal 20px/1 dashicons; speak: none; display: inline-block; margin-left: -1px; padding-right: 3px; vertical-align: top; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }');
		});
	}, 10, 2);
}