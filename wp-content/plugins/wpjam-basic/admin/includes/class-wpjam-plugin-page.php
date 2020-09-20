<?php
class WPJAM_Plugin_Page{
	private static $render			= true;
	private static $page_setting	= null;
	private static $query_data		= [];
	private static $menu_pages		= [];

	public  static function render($is_render=null){
		if(!is_null($is_render)){
			$old_render	= self::$render;
			self::$render	= $is_render;

			return boolval($old_render);
		}

		return boolval(self::$render);
	}

	public  static function init(){
		global $plugin_page;

		if(empty($plugin_page) && !self::render()){
			return;
		}

		$wpjam_pages	= self::get_menu_pages();

		if(!$wpjam_pages){
			return;
		}

		$builtin_parent_pages	= self::get_builtin_parent_pages();

		foreach ($wpjam_pages as $menu_slug=>$wpjam_page) {
			if(isset($builtin_parent_pages[$menu_slug])){
				$parent_slug	= $builtin_parent_pages[$menu_slug];
			}else{
				if(empty($wpjam_page['menu_title'])){
					continue;
				}
				
				$wpjam_page	= self::parse_page($menu_slug, $wpjam_page);

				if($plugin_page == $menu_slug){
					self::set_current_admin_url($plugin_page);

					self::$page_setting	= $wpjam_page;
				}

				$parent_slug	= $menu_slug;
			}

			if(!empty($wpjam_page['subs'])){
				foreach ($wpjam_page['subs'] as $sub_menu_slug => $sub_page) {
					$sub_page	= self::parse_page($sub_menu_slug, $sub_page, $parent_slug);

					if($plugin_page == $sub_menu_slug){
						$parent_page	= in_array($parent_slug, $builtin_parent_pages) ? $parent_slug : 'admin.php';

						self::set_current_admin_url($plugin_page, $parent_page);

						self::$page_setting	= $sub_page;

						if(!self::render()){
							break;
						}
					}
				}	
			}

			if(!self::render() && self::$page_setting){
				break;
			}
		}
	}

	public static function add_menu_page($menu_slug, $args=[]){
		self::$menu_pages[$menu_slug]	= $args;
	}

	public static function get_menu_pages(){
		if(is_multisite() && is_network_admin()){
			return apply_filters('wpjam_network_pages', []);
		}else{
			$menu_pages	= self::$menu_pages;
			
			if(!empty(self::$option_settings)){
				foreach (self::$option_settings as $option_name => $args){
					if(!empty($args['post_type'])){
						$menu_pages[$args['post_type'].'s']['subs'][$option_name] = ['menu_title' => $args['title'],	'function'=>'option'];
					}
				}
			}

			return apply_filters('wpjam_pages', $menu_pages);
		}
	}

	public  static function render_page(){
		echo '<div class="wrap">';
		
		self::plugin_page(self::$page_setting);

		echo '</div>';
	}

	public  static function load($page_setting=[], $in_tab=false){
		global $plugin_page, $current_tab, $current_admin_url;

		$page_setting	= $page_setting ?: self::$page_setting;
		$file_key		= $in_tab ? 'tab_file' : 'page_file';

		if(!empty($page_setting[$file_key]) && file_exists($page_setting[$file_key])){
			include $page_setting[$file_key];
		}

		if($in_tab){
			do_action('wpjam_plugin_page_load', $plugin_page, $current_tab);
		}else{
			do_action('wpjam_plugin_page_load', $plugin_page, '');
		}

		if(!empty($page_setting['query_args'])){
			foreach($page_setting['query_args'] as $query_arg) {
				self::$query_data[$query_arg]	= wpjam_get_data_parameter($query_arg);
			}

			$current_admin_url	= add_query_arg(self::$query_data, $current_admin_url);
		}

		$page_hook	= self::$page_setting['page_hook'] ?? '';
		$function	= $page_setting['function'] ?? null;

		if($function == 'list' || $function == 'list_table'){
			global $current_list_table;

			$current_list_table	= $page_setting['list_table_name'] ?? $plugin_page;

			if(wp_doing_ajax()){
				add_action('wp_ajax_wpjam-list-table-action', [__CLASS__, 'list_table_ajax_response']);	
			}else{
				if($page_hook){
					add_action('load-'.$page_hook, [__CLASS__, 'list_table']);
				}	
			}
		}elseif($function == 'option'){
			global $current_option;

			$current_option	= $page_setting['option_name'] ?? $plugin_page;

			if(wp_doing_ajax()){
				add_action('wp_ajax_wpjam-option-action', [__CLASS__, 'option_ajax_response']);
			}else{
				if($page_hook){
					add_action('load-'.$page_hook, [__CLASS__,'option_register_settings']);
				}

				add_action('admin_action_update', [__CLASS__, 'option_register_settings']);
			}	
		}elseif($function == 'dashboard'){
			global $current_dashboard, $plugin_page;

			$current_dashboard	= $page_setting['dashboard_name'] ?? $plugin_page;
		}elseif($function == 'tab'){
			if($in_tab){
				wp_die('tab 不能嵌套 tab');
			}

			$tabs	= $page_setting['tabs'] ?? [];
			$tabs	= apply_filters(wpjam_get_filter_name($plugin_page, 'tabs'), $tabs);

			if($tabs){
				$tab_keys	= array_map('sanitize_key', array_keys($tabs));
				$tabs		= array_combine($tab_keys, array_values($tabs));

				if(self::render()){
					$current_tab	= wpjam_get_parameter('tab', ['sanitize_callback'=>'sanitize_key']);
					$current_tab	= $current_tab ?: $tab_keys[0];
				}else{
					$current_tab	= wpjam_get_parameter('current_tab', ['method'=>'POST', 'sanitize_callback'=>'sanitize_key']);
				}
				
				if(empty($current_tab) || empty($tabs[$current_tab])){
					$wp_error	= new WP_Error('invalid_tab', 'Tabs 非法 Tab');
				}
			}else{
				$wp_error	= new WP_Error('empty_tabs', 'Tabs 未设置');
			}

			if(isset($wp_error)){
				if(wp_doing_ajax()){
					wpjam_send_json($wp_error);
				}else{
					wp_die($wp_error);
				}
			}

			self::$page_setting['tabs']		= $tabs;
			self::$page_setting['tab_url']	= $current_admin_url;

			$current_admin_url	= $current_admin_url.'&tab='.$current_tab;

			self::load($tabs[$current_tab], true);
		}
	}

	private static function plugin_page($page_setting, $in_tab=false){
		$function	= $page_setting['function'] ?? null;

		if($function == 'list' || $function == 'list_table'){
			self::list_table_page($page_setting);
		}elseif($function == 'option'){
			self::option_page($page_setting);
		}elseif($function == 'dashboard'){
			self::dashboard_page($page_setting);
		}elseif($function == 'tab'){
			self::tab_page($page_setting);
		}else{
			self::page_title($page_setting);

			if(empty($function)){
				if($in_tab){
					wp_die('tab 未设置 function');
				}

				global $plugin_page;

				$function	= wpjam_get_filter_name($plugin_page, 'page');
			}

			if(is_callable($function)){
				call_user_func($function);
			}else{
				wp_die($function.'不存在');
			}
		}
	}

	private static function tab_page($page_setting){
		global $plugin_page, $current_tab;

		$function	= wpjam_get_filter_name($plugin_page, 'page');	// 所有 Tab 页面都执行的函数

		if(count($page_setting['tabs']) > 1){

			$summary	= $page_setting['summary'] ?? '';
			
			self::page_title($page_setting, '', $summary);
			
			if(is_callable($function)){
				call_user_func($function);
			}

			echo '<nav class="nav-tab-wrapper wp-clearfix">';
			
			foreach ($page_setting['tabs'] as $tab_key => $tab) {
				$class	= 'nav-tab';
			
				if($current_tab == $tab_key){
					$class	.= ' nav-tab-active';
				}

				echo '<a class="'.$class.'" href="'.$page_setting['tab_url'].'&tab='.$tab_key.'">'.$tab['title'].'</a>';
			}

			echo '</nav>';
		}else{
			if(is_callable($function)){
				call_user_func($function);
			}
		}

		self::plugin_page($page_setting['tabs'][$current_tab], true);
	}

	public  static function list_table_ajax_response(){
		global $wpjam_list_table, $current_list_table;

		$wpjam_list_table	= WPJAM_List_Table::get_instance($current_list_table, self::$query_data);

		if(is_wp_error($wpjam_list_table)){
			wpjam_send_json($wpjam_list_table);
		}else{
			$wpjam_list_table->ajax_response();
		}
	}

	public  static function list_table(){
		global $wpjam_list_table, $current_list_table;

		$wpjam_list_table	= WPJAM_List_Table::get_instance($current_list_table, self::$query_data);

		$errmsg	= '';

		if(is_wp_error($wpjam_list_table)){
			$errmsg	= $wpjam_list_table->get_error_message();
		}else{
			$result = $wpjam_list_table->prepare_items();

			if(is_wp_error($result)){
				$errmsg	= $result->get_error_message();
			}
		}

		if($errmsg){
			echo '<div class="list-table-notice notice notice-error is-dismissible"><p>'.$errmsg.'</p></div>';
		}
	}

	private static function list_table_page($page_setting=[]){
		global $wpjam_list_table;

		if($wpjam_list_table && !is_wp_error($wpjam_list_table)){
			self::page_title($wpjam_list_table->_args['title'], $wpjam_list_table->get_subtitle(), $wpjam_list_table->get_summary());

			$wpjam_list_table->list_page();
		}else{
			self::page_title($page_setting);
		}
	}

	public  static function option_ajax_response(){
		global $current_option;

		self::option_register_settings();

		$wpjam_setting	= wpjam_get_option_setting($current_option);
		$capability		= $wpjam_setting['capability'] ?: 'manage_options';

		if(!current_user_can($capability)){
			wpjam_send_json([
				'errcode'	=> 'bad_authentication',
				'errmsg'	=> '无权限'
			]);
		}

		$_POST	= wp_parse_args($_POST['data']);

		$option_page	= $_POST['option_page'];

		if(!wp_verify_nonce($_POST['_wpnonce'], $option_page.'-options')){
			wpjam_send_json([
				'errcode'	=> 'invalid_nonce',
				'errmsg'	=> '非法操作'
			]);
		}

		if(has_filter('allowed_options')){
			$allowed_options = apply_filters('allowed_options', []);
		}else{
			$allowed_options = apply_filters('whitelist_options', []);
		}

		$options	= $allowed_options[$option_page];

		if(empty($options)){
			wpjam_send_json([
				'errcode'	=> 'invalid_option',
				'errmsg'	=> '字段未注册'
			]);
		}

		foreach ( $options as $option ) {
			$option = trim( $option );
			$value = null;
			if ( isset( $_POST[ $option ] ) ) {
				$value = $_POST[ $option ];
				if ( ! is_array( $value ) ) {
					$value = trim( $value );
				}
				$value = wp_unslash( $value );
			}

			update_option($option, $value);
		}

		if($settings_errors = get_settings_errors()){
			$errmsg = '';

			foreach ($settings_errors as $key => $details) {
				if (in_array($details['type'], ['updated', 'success', 'info'])) {
					continue;
				}

				$errmsg	.= $details['message'].'&emsp;';
			}

			wpjam_send_json(['errcode'=>'update_failed', 'errmsg'=>$errmsg]);
		}else{
			$data = get_option($option);

			wpjam_send_json(['data'=>$data]);
		}	
	}

	public  static function option_register_settings(){
		global $plugin_page, $current_option;

		$wpjam_setting = wpjam_get_option_setting($current_option);

		if(!$wpjam_setting) {
			return;
		}

		$option_blog_id	= $wpjam_setting['blog_id'] ?? '';
		$switched		= (is_multisite() && $option_blog_id) ? switch_to_blog($option_blog_id) : false;

		$capability		= $wpjam_setting['capability'];
		if($capability != 'manage_options'){
			add_filter('option_page_capability_'.$wpjam_setting['option_page'], function() use($capability){
				return $capability; 
			});	
		}

		$option_type	= $wpjam_setting['option_type'];
		$option_group	= $wpjam_setting['option_group'];
		$sections		= $wpjam_setting['sections'];

		$args	= [
			'option_type'		=> $option_type,
			'sanitize_callback'	=> $wpjam_setting['sanitize_callback'] ?? [__CLASS__, 'option_sanitize_callback']
		];

		// 只需注册字段，add_settings_section 和 add_settings_field 可以在具体设置页面添加
		if($option_type == 'array'){
			$args['fields']	= array_merge(...array_column($sections, 'fields'));

			register_setting($option_group, $current_option, $args);	
		}else{
			foreach ($sections as $section_id => $section) {
				foreach ($section['fields'] as $key => $field) {
					if($field['type'] == 'fieldset'){
						$fieldset_type	= $field['fieldset_type'] ?? 'single';
						if($fieldset_type == 'single'){
							foreach ($field['fields'] as $sub_key => $sub_field) {
								$args['fields']	= [$sub_key => $sub_field];

								register_setting($option_group, $sub_key, $args);
							}

							continue;
						}
					}

					$args['fields']	= [$key => $field];
					register_setting($option_group, $key, $args);
				}
			}
		}
	}

	public  static function option_sanitize_callback($value, $option_name=''){
		$option_name	= $option_name ?: str_replace('sanitize_option_', '', current_filter());
		$registered		= get_registered_settings();
		$option_args	= $registered[$option_name] ?? [];
		
		if(empty($option_args)){
			return $value;
		}

		$option_type	= $option_args['option_type'];
		$values			= $option_type == 'array' ? $value : [$option_name=>$value];
		$values			= wpjam_validate_fields_value($option_args['fields'], $values);

		if(is_wp_error($values)){
			add_settings_error($option_name, $values->get_error_code(), $values->get_error_message());

			return get_option($option_name);
		}else{
			return $option_type == 'array' ?  $values+wpjam_get_option($option_name) : $values[$option_name];
		}
	}

	// 部分代码拷贝自 do_settings_sections 和 do_settings_fields 函数
	private static function option_page($page_setting=[]){
		global $current_option, $current_tab;

		if(empty($current_option)){
			return;
		}

		$wpjam_setting	= wpjam_get_option_setting($current_option);

		if(!$wpjam_setting)	{
			wp_die($current_option.' 的 wpjam_settings 未设置', '未设置');
		}

		$option_blog_id	= $wpjam_setting['blog_id'] ?? '';
		$switched		= (is_multisite() && $option_blog_id) ? switch_to_blog($option_blog_id) : false;

		$option_type	= $wpjam_setting['option_type'];
		$option_group	= $wpjam_setting['option_group'];
		$option_page	= $wpjam_setting['option_page'];
		$sections		= $wpjam_setting['sections'];

		do_action_deprecated(str_replace('-', '_', $option_page).'_option_page', [], 'WPJAM Basic 3.9', '<code>admin_head</code>hook插入JS CSS，或<code>summary</code>参数插入其他内容');

		$summary	= $wpjam_setting['summary'] ?? null;

		self::page_title($page_setting, '', $summary);

		$page_type	= count($sections) > 1 ? 'tab' : '';

		if($page_type == 'tab'){
			echo '<div class="tabs">';

			echo '<h2 class="nav-tab-wrapper wp-clearfix"><ul>';
			foreach ( $sections as $section_id => $section ) {
				$attr	= WPJAM_Field::parse_wrap_attr($section);

				echo '<li id="tab_title_'.$section_id.'" '.$attr.'><a class="nav-tab" href="#tab_'.$section_id.'">'.$section['title'].'</a></li>';
			}
			echo '</ul></h2>';
		}

		if(is_multisite() && is_network_admin()){	
			if($_SERVER['REQUEST_METHOD'] == 'POST'){	// 如果是 network 就自己保存到数据库	
				$fields	= array_merge(...array_column($sections, 'fields'));
				$value	= wpjam_validate_fields_value($fields, $_POST[$current_option]);
				$value	= $value+wpjam_get_option($current_option);

				update_site_option($current_option,  $value);
				
				echo '<div class="notice notice-success is-dismissible"><p>设置已保存。</p></div>';
			}
			
			echo '<form action="'.add_query_arg(['settings-updated'=>'true'], wpjam_get_current_page_url()).'" method="POST">';
		}else{
			$attr	= $wpjam_setting['ajax'] ? ' id="wpjam_option"' : '';

			echo '<form action="options.php" method="POST"'.$attr.'>';
			
			settings_errors();
		}

		if(!$wpjam_setting['ajax']){
			echo '<input type="hidden" name="screen_id" value="'.get_current_screen()->id.'" />';

			if($current_tab){
				echo '<input type="hidden" name="current_tab" value="'.$current_tab.'" />';
			}
		}
		
		settings_fields($option_group);
		foreach($sections as $section_id => $section) {
			echo '<div id="tab_'.$section_id.'"'.'>';

			if(!empty($section['title'])){
				if(empty($current_tab)){
					echo '<h2>'.$section['title'].'</h2>';
				}else{
					echo '<h3>'.$section['title'].'</h3>';
				}
			}

			if(!empty($section['callback'])) {
				call_user_func($section['callback'], $section);
			}

			if(!empty($section['summary'])) {
				echo wpautop($section['summary']);
			}
			
			if(!$section['fields']) {
				echo '</div>';
				continue;
			}

			if($option_type == 'array'){
				wpjam_fields($section['fields'], array(
					'fields_type'	=> 'table',
					'data_type'		=> 'option',
					'option_type'	=> 'array',
					'option_name'	=> $current_option
				));
			}else{
				wpjam_fields($section['fields'], array(
					'fields_type'	=> 'table',
					'data_type'		=> 'option',
					'option_type'	=> 'single'
				));
			}
			
			echo '</div>';
		}

		if($page_type == 'tab'){
			echo '</div>';
		}
		
		echo '<p class="submit">';
		submit_button('', 'primary', 'submit', false);
		echo '<span class="spinner"  style="float: none; height: 28px;"></span>';
		echo '</p>';

		echo '</form>'; 

		if($switched){
			restore_current_blog();
		}
	}

	private static function dashboard_page($page_setting=[]){
		global $current_dashboard;

		require_once ABSPATH . 'wp-admin/includes/dashboard.php';
		
		// wp_dashboard_setup();

		$dashboard_widgets	= $page_setting['widgets'] ?? [];
		$dashboard_widgets	= apply_filters(wpjam_get_filter_name($current_dashboard,'dashboard_widgets'), $dashboard_widgets);

		if($dashboard_widgets){
			wpjam_admin_add_dashboard_widgets($dashboard_widgets);
		}

		wp_enqueue_script('dashboard');
		
		if(wp_is_mobile()) {
			wp_enqueue_script('jquery-touch-punch');
		}

		$filter_name	= wpjam_get_filter_name($current_dashboard, 'welcome_panel');
		
		if(has_action($filter_name)){
			echo '<div id="welcome-panel" class="welcome-panel">';
			do_action($filter_name);
			echo '</div>';
		}else{
			self::page_title($page_setting);
		} 

		echo '<div id="dashboard-widgets-wrap">';
		wp_dashboard();
		echo '</div>';
	}

	private static function page_title($page_title, $subtitle='', $summary=null){
		global $current_tab;

		if(is_array($page_title)){
			$page_setting	= $page_title;
			$page_title		= $page_setting['page_title'] ?? $page_setting['title'];

			if(!empty($page_setting['function']) && $page_setting['function'] == 'tab'){
				$doing_tab_title	= true;
			}

			if(is_null($summary)){
				if(!empty($page_setting['summary'])){
					$summary	= $page_setting['summary'];
				}else{
					$summary	= apply_filters('wpjam_plugin_page_summary', '');
				}
			}
		}

		if($page_title){
			if(empty($doing_tab_title) && $current_tab && count(self::$page_setting['tabs']) > 1){
				echo '<h2>'.$page_title.$subtitle.'</h2>';
			}else{
				echo '<h1 class="wp-heading-inline">'.$page_title.'</h1>';
				echo $subtitle;
				echo '<hr class="wp-header-end">';
			}
		}

		if($summary){
			echo wpautop($summary);
		}
	}

	private static function parse_page($menu_slug, $wpjam_page, $parent_slug=''){
		$menu_title	= $wpjam_page['menu_title'] ?? '';
		$page_title	= $wpjam_page['page_title'] = $wpjam_page['page_title']?? $menu_title;
		$capability	= $wpjam_page['capability'] ?? 'manage_options';

		if(self::render()){
			if($parent_slug){
				$page_hook	= add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, [__CLASS__, 'render_page']);
			}else{
				$icon		= $wpjam_page['icon'] ?? '';
				$position	= $wpjam_page['position'] ?? '';

				$page_hook	= add_menu_page($page_title, $menu_title, $capability, $menu_slug, [__CLASS__, 'render_page'], $icon, $position);
			}

			$wpjam_page['page_hook']	= $page_hook;
		}

		return $wpjam_page;
	}

	private static function set_current_admin_url($plugin_page, $parent_page='admin.php'){
		global $current_admin_url;

		$current_admin_url	= add_query_arg(['page'=>$plugin_page], $parent_page);
		$current_admin_url	= is_network_admin() ? network_admin_url($current_admin_url) : admin_url($current_admin_url);
	}

	private static function get_builtin_parent_pages(){
		if(is_multisite() && is_network_admin()){
			return [
				'settings'	=> 'settings.php',
				'theme'		=> 'themes.php',
				'themes'	=> 'themes.php',
				'plugins'	=> 'plugins.php',
				'users'		=> 'users.php',
				'sites'		=> 'sites.php',
			];
		}else{
			$builtin_parent_pages	= [
				'dashboard'	=> 'index.php',
				'management'=> 'tools.php',
				'options'	=> 'options-general.php',
				'theme'		=> 'themes.php',
				'themes'	=> 'themes.php',
				'plugins'	=> 'plugins.php',
				'posts'		=> 'edit.php',
				'media'		=> 'upload.php',
				'links'		=> 'link-manager.php',
				'pages'		=> 'edit.php?post_type=page',
				'comments'	=> 'edit-comments.php',
				'users'		=> current_user_can('edit_users') ? 'users.php' : 'profile.php',
			];
			
			if($custom_post_types = get_post_types(['_builtin'=>false, 'show_ui'=>true])){
				foreach ($custom_post_types as $custom_post_type) {
					$builtin_parent_pages[$custom_post_type.'s'] = 'edit.php?post_type='.$custom_post_type;
				}
			}

			return $builtin_parent_pages;
		}
	}

	public static function page_action_ajax_response(){
		global $plugin_page;

		$action	= $_POST['page_action'];
		$nonce	= $_POST['_ajax_nonce'];

		if(!wp_verify_nonce($nonce, $plugin_page.'-'.$action)){
			wpjam_send_json([
				'errcode'	=> 'invalid_nonce',
				'errmsg'	=> '非法操作'
			]);
		}

		$action_type	= sanitize_key($_POST['page_action_type']);

		do_action('wpjam_page_action', $action, $action_type);

		$ajax_response	= wpjam_get_filter_name($plugin_page, 'ajax_response');
		$ajax_response	= apply_filters('wpjam_page_ajax_response', $ajax_response, $plugin_page, $action, $action_type);

		if(is_callable($ajax_response)){
			$result	= call_user_func($ajax_response, $action);
			if(is_wp_error($result)){
				wpjam_send_json($result);
			}else{
				$result	= is_array($result) ? $result : [];
				wpjam_send_json($result);
			}
		}else{
			wpjam_send_json([
				'errcode'	=> 'invalid_ajax_response',
				'errmsg'	=> '非法回调函数'
			]);
		}
	}
}