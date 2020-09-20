<?php
/*
Plugin Name: 移动主题
Plugin URI: http://blog.wpjam.com/project/wpjam-basic/
Description: 给移动设备设置单独的主题，以及在PC环境下进行移动主题的配置。
Version: 1.0
*/

add_action('plugins_loaded', function(){
	if(wp_is_mobile() || (is_admin() && wpjam_basic_get_setting('admin_mobile_theme'))){
		
		if(wpjam_basic_get_setting('mobile_stylesheet')){
			add_filter('stylesheet', function($stylesheet){
				return wpjam_basic_get_setting('mobile_stylesheet');
			});
		}
		
		if(wpjam_basic_get_setting('mobile_template')){
			add_filter('template', function($template){
				return wpjam_basic_get_setting('mobile_template');
			});
		}
	}	
}, 0);

if(is_admin()){
	add_filter('wpjam_pages', function($wpjam_pages){
		$wpjam_pages['themes']['subs']['mobile-theme']	=[
			'menu_title'	=>'移动主题',
			'function'		=>'option',
			'option_name'	=>'wpjam-basic'
		];

		return $wpjam_pages;
	});

	add_action('wpjam_plugin_page_load', function($plugin_page){
		if($plugin_page != 'mobile-theme'){
			return;
		}

		add_filter('wpjam_basic_setting', function(){
			$themes		= wp_get_themes();
			$current	= wp_get_theme();

			$theme_options		= [];
			$theme_options[$current->get_stylesheet()]	= $current->get('Name');

			foreach($themes as $theme){
				$theme_options[$theme->get_stylesheet()]	= $theme->get('Name');
			}

			$fields		= [];
			$fields['mobile_stylesheet']	= ['title'=>'选择移动主题',	'type'=>'select',	'options'=>$theme_options];

			if(wpjam_basic_get_setting('mobile_stylesheet')){
				$fields['admin_mobile_theme']	= ['title'=>'设置移动主题',	'type'=>'view',	'value'=>wpjam_get_admin_mobile_theme_action()];
			}

			if(wpjam_basic_get_setting('admin_mobile_theme')){
				$summary	= '使用手机和平板访问网站的用户将看到以下选择的主题界面，而桌面用户依然看到 PC 主题界面。';
			}else{
				$summary	= '使用手机和平板访问网站的用户将看到以下选择的主题界面，而桌面用户依然看到 <strong>'.$current->get('Name').'</strong> 主题界面。';
			}

			$sections	= [
				'mobile-theme'	=> [
					'title'		=> '', 
					'summary'	=> $summary,
					'fields'	=> $fields
				]
			];

			$ajax	= false;

			return compact('sections', 'ajax');
		});

		add_action('sanitize_option_wpjam-basic', function($value){
			$mobile_stylesheet = $value['mobile_stylesheet'] ?? '';

			if($mobile_stylesheet){
				$mobile_theme	= wp_get_theme($mobile_stylesheet);
				$value['mobile_template']	= $mobile_theme->get_template();
			}

			return $value;
		});

		function wpjam_get_admin_mobile_theme_action(){
			$admin_mobile_theme	= wpjam_basic_get_setting('admin_mobile_theme');

			$mobile_theme		= wp_get_theme(wpjam_basic_get_setting('mobile_stylesheet'));
			$mobile_theme_name	= $mobile_theme->get('Name');

			$button		= $admin_mobile_theme ? '关闭' : '开启';
			$button		= wpjam_get_ajax_button(['action'=>'toggle_admin_theme', 'direct'=>true, 'button_text'=>$button.'在后台启用移动主题', 'class'=>'']);

			$action	= $admin_mobile_theme ? '刷新后台即可开始设置移动主题的选项，设置完成之后，' : '<span id="admin_mobile_theme">如要设置移动主题的选项，';

			return '<span id="admin_mobile_theme">' . $action . $button . '。</span>';
		}


		add_action('wpjam_page_action', function($action){
			if($action == 'toggle_admin_theme'){
				$admin_mobile_theme	= wpjam_basic_get_setting('admin_mobile_theme');
				$admin_mobile_theme	= $admin_mobile_theme ? 0 : 1;

				wpjam_basic_update_setting('admin_mobile_theme', $admin_mobile_theme);

				wpjam_send_json(['summary'=>wpjam_get_admin_mobile_theme_action()]);
			}
		});

		add_action('admin_head',function(){
			?>

			<script type="text/javascript">
			jQuery(function($){
				$('body').on('page_action_success', function(e, response){
					var action		= response.page_action;
					var summary		= response.summary;

					if(action == 'toggle_admin_theme'){	
						$('#admin_mobile_theme').html(summary);
						// $('#admin_mobile_theme').css('background-color','#ffffee');
					}
				});
			});
			</script>
			<?php
		});
	});
}

