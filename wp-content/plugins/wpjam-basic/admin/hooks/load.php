<?php
add_action('wp_loaded', function(){	// 内部的 hook 使用 优先级 9，因为内嵌的 hook 优先级要低
	global $pagenow;

	if($pagenow == 'options.php'){
		// 为了实现多个页面使用通过 option 存储。
		// 注册设置选项，选用的是：'admin_action_' . $_REQUEST['action'] hook，
		// 因为在这之前的 admin_init 检测 $plugin_page 的合法性
		add_action('admin_action_update', function(){
			global $plugin_page;

			$referer_origin	= parse_url(wpjam_get_referer());

			if(empty($referer_origin['query']))	{
				return;
			}

			$referer_args	= wp_parse_args($referer_origin['query']);
			$plugin_page	= $referer_args['page'] ?? '';	// 为了实现多个页面使用通过 option 存储。

			WPJAM_Plugin_Page::render(false);
			WPJAM_Plugin_Page::init();

			set_current_screen($_POST['screen_id']);
		}, 9);
	}elseif(wp_doing_ajax()){
		add_action('admin_init', function(){
			global $plugin_page;

			if(isset($_POST['plugin_page'])){
				$plugin_page	= $_POST['plugin_page'];
			}
			
			WPJAM_Plugin_Page::render(false);
			WPJAM_Plugin_Page::init();

			if(isset($_POST['screen_id'])){
				set_current_screen($_POST['screen_id']);
			}elseif(isset($_POST['screen'])){
				set_current_screen($_POST['screen']);	
			}else{
				$ajax_action	= $_POST['action'] ?? '';

				if($ajax_action == 'inline-save-tax'){
					set_current_screen('edit-'.sanitize_key($_POST['taxonomy']));
				}elseif($ajax_action == 'get-comments'){
					set_current_screen('edit-comments');
				}
			}

		}, 9);

		add_action('wp_ajax_wpjam-query', 		['WPJAM_Builtin_Page', 'data_query_ajax_response']);
		add_action('wp_ajax_wpjam-page-action', ['WPJAM_Plugin_Page', 'page_action_ajax_response']);
	}else{
		if(is_multisite() && is_network_admin()){
			add_action('network_admin_menu', ['WPJAM_Plugin_Page', 'init'], 9);	
		}else{
			add_action('admin_menu', ['WPJAM_Plugin_Page', 'init'], 9);	
		}
	}
			
	add_action('current_screen', function ($current_screen=null){	
		global $plugin_page;

		if(!empty($plugin_page)){
			WPJAM_Plugin_Page::load();
		}else{
			WPJAM_Builtin_Page::load($current_screen);
		}
	}, 9);
});

add_action('admin_enqueue_scripts', function(){
	global $pagenow, $current_screen;

	wp_dequeue_style('ie');	// IE 7 ... 没人用了吧？

	if($pagenow == 'customize.php'){
		return;
	}

	$plugin_data	= get_plugin_data(WPJAM_BASIC_PLUGIN_FILE);
	$ver			= $plugin_data['Version'];

	// wp_enqueue_script('jquery-ui-button');
	add_thickbox();

	$post = get_post();
	if(!$post && !empty($GLOBALS['post_ID'])){
		$post = $GLOBALS['post_ID'];
	}

	wp_enqueue_media(['post'=>$post]);

	wp_enqueue_style('morris',			'https://cdn.staticfile.org/morris.js/0.5.1/morris.css', [], $ver);
	wp_enqueue_style('wpjam-style',		WPJAM_BASIC_PLUGIN_URL.'/static/style.css', ['wp-color-picker', 'editor-buttons'], $ver);

	wp_enqueue_script('wpjam-script',	WPJAM_BASIC_PLUGIN_URL.'/static/script.js', ['jquery', 'thickbox'], $ver);
	wp_enqueue_script('wpjam-form',		WPJAM_BASIC_PLUGIN_URL.'/static/form.js',   ['wpjam-script', 'wp-backbone', 'jquery-ui-tabs', 'jquery-ui-autocomplete', 'wp-color-picker', 'mce-view'], $ver);

	wp_enqueue_script('raphael',		'https://cdn.staticfile.org/raphael/2.3.0/raphael.min.js', [], $ver);
	wp_enqueue_script('morris',			'https://cdn.staticfile.org/morris.js/0.5.1/morris.min.js', ['raphael'], $ver);

	if($current_screen->base == 'edit'){
		wp_enqueue_script('wpjam-posts',WPJAM_BASIC_PLUGIN_URL.'/static/posts.js',	['wpjam-form'], $ver, true);
	}

	global $plugin_page, $current_tab, $current_list_table, $current_option;

	$item_prefix	= '.tr-';

	if($plugin_page){
		if(isset($current_option)){
			$params	= [];
		}else{
			$params	= $_REQUEST;

			foreach (['page', 'tab', '_wp_http_referer', '_wpnonce'] as $query_key) {
				unset($params[$query_key]);
			}

			$params	= $params ? array_map('sanitize_textarea_field', $params) : [];
		}
	}else{
		$params	= null;	

		if(in_array($pagenow, ['upload.php', 'edit.php'])){
			$item_prefix	= '#post-';
		}elseif($pagenow == 'edit-tags.php'){
			$item_prefix	= '#tag-';
		}elseif($pagenow == 'users.php'){
			$item_prefix	= '#user-';
		}
	}

	$params	= $params ?: new stdClass();

	wp_localize_script('wpjam-script', 'wpjam_page_setting', [
		'screen_id'			=> $current_screen->id,
		'screen_base'		=> $current_screen->base,
		'plugin_page'		=> $plugin_page ?? null,
		'current_tab'		=> $current_tab ?? null,
		'current_list_table'=> $current_list_table ?? null,
		'current_option'	=> $current_option ?? null,
		'params'			=> $params,
		'item_prefix'		=> $item_prefix
	]);
});

// 如果插件页面
add_filter('set-screen-option', function($status, $option, $value){
	return isset($_GET['page']) ? $value : $status;
}, 10, 3);

//模板 JS
add_action('print_media_templates', function(){ ?>

	<div id="tb_modal" style="display:none; background: #f1f1f1;"></div>

	<?php echo WPJAM_Field::get_field_tmpls();
});


function wpjam_admin_tooltip($text, $tooltip){
	return '<span class="wpjam-tooltip">'.$text.'<span class="wpjam-tooltip-text">'.$tooltip.'</span></span>';
}

function wpjam_get_ajax_button($args){
	return WPJAM_Form::ajax_button($args);
}

function wpjam_get_ajax_form($args){
	return WPJAM_Form::ajax_form($args);
}

function wpjam_ajax_button($args){
	echo wpjam_get_ajax_button($args);
}

function wpjam_ajax_form($args){
	echo wpjam_get_ajax_form($args);
}

// 获取页面来源
function wpjam_get_referer(){
	$referer	= wp_get_original_referer();
	$referer	= $referer?:wp_get_referer();

	$removable_query_args	= array_merge(wp_removable_query_args(), ['_wp_http_referer', 'action', 'action2', '_wpnonce']);

	return remove_query_arg($removable_query_args, $referer);	
}

function wpjam_get_list_table_filter_link($filters, $title, $class=''){
	global $wpjam_list_table;
	return $wpjam_list_table->get_filter_link($filters, $title, $class);
}

function wpjam_get_list_table_row_action($action, $args=[]){
	global $wpjam_list_table;
	return $wpjam_list_table->get_row_action($action, $args);
}

function wpjam_admin_add_dashboard_widgets($dashboard_widgets){
	foreach ($dashboard_widgets as $widget_id => $meta_box) {
		$meta_box = wp_parse_args($meta_box, [
			'title'		=> '',
			'control'	=> null,
			'context'	=> 'normal',	// 位置，normal 左侧, side 右侧
			'priority'	=> 'core',
			'args'		=> [],
			'callback'	=> wpjam_get_filter_name($widget_id,'dashboard_widget_callback')
		]);
		
		add_meta_box($widget_id, $meta_box['title'], $meta_box['callback'], get_current_screen(), $meta_box['context'], $meta_box['priority'], $meta_box['args']);
	}
}

// 自定义主题更新
/* 数据格式：
{
	theme: "Autumn",
	new_version: "2.0.1",
	url: "http://www.xintheme.com/theme/4893.html",
	package: "http://www.xintheme.com/download/Autumn.zip"
}
*/
function wpjam_register_theme_upgrader($upgrader_url){
	add_filter('site_transient_update_themes',  function($transient) use($upgrader_url){
		$theme	= get_template();

		if(empty($transient->checked[$theme])){
			return $transient;
		}
		
		$remote	= get_transient('wpjam_theme_upgrade_'.$theme);

		if(false == $remote){
			$remote = wpjam_remote_request($upgrader_url);
	 
			if(!is_wp_error($remote)){
				set_transient( 'wpjam_theme_upgrade_'.$theme, $remote, HOUR_IN_SECONDS*12 );
			}
		}

		if($remote && !is_wp_error($remote)){
			if(version_compare( $transient->checked[$theme], $remote['new_version'], '<' )){
				$transient->response[$theme]	= $remote;
			}
		}

		return $transient;
	});
}

