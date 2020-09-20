<?php
// 设置菜单
function wpjam_basic_admin_pages($wpjam_pages){
	$wpjam_pages['users']['subs']['wpjam-messages'] 		= [
		'menu_title'	=> '站内消息',
		'capability'	=> 'read',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-messages.php'
	];
		
	$capability	= is_multisite() ? 'manage_sites' : 'manage_options';
	$subs		= [];

	$subs['wpjam-basic']	= [
		'menu_title'	=> '优化设置',	
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-basic.php',
		'summary'		=> '优化设置让你通过关闭一些不常用的功能来加快  WordPress 的加载。
	但是某些功能的关闭可能会引起一些操作无法执行，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-basic-optimization-setting/" target="_blank">优化设置</a>。'
	];

	$subs['wpjam-custom']	= [
		'menu_title'	=> '样式定制', 
		'function'		=> 'option',
		'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-custom.php',
		'summary'		=> '对网站的前端或者后台的样式进行定制，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-basic-custom-setting/"  target="_blank">样式定制</a>。'
	];
	
	$verified	= WPJAM_Verify::verify();

	if(!$verified){
		$subs['wpjam-verify']	= [
			'menu_title'	=> '扩展管理',
			'page_title'	=> '验证 WPJAM',
			'function'		=> 'wpjam_verify_page',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-verify.php'
		];
	}else{
		$subs['wpjam-cdn']	= [
			'menu_title'	=> 'CDN加速', 
			'function'		=> 'option',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-cdn.php',
			'summary'		=> 'CDN 加速让你使用云存储对博客的静态资源进行 CDN 加速，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-basic-cdn/" target="_blank">CDN 加速</a>。'
		];

		$subs['wpjam-thumbnail']	= [
			'menu_title'	=> '缩略图设置', 
			'function'		=> 'option',
			'option_name'	=> 'wpjam-cdn',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-thumbnail.php',
			'summary'		=> '详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-basic-thumbnail/" target="_blank">缩略图设置</a>，启用之后，请使用 <a href="https://blog.wpjam.com/m/wpjam-basic-thumbnail-functions/" target="_blank">WPJAM 的相关缩略图</a>函数代替 WordPress 自带的缩略图函数。'
		];

		$subs['wpjam-posts']	= [
			'menu_title'	=> '文章设置', 
			'summary'		=> '文章设置优化和增强文章列表和文章功能。',
			'function'		=> 'tab',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-posts.php'
		];

		global $wpjam_basic_sub_pages;

		if(!empty($wpjam_basic_sub_pages)){
			$subs	+= $wpjam_basic_sub_pages;
		}

		$subs	= apply_filters('wpjam_basic_sub_pages', $subs);

		$subs['server-status']	= [
			'menu_title'	=> '系统信息',		
			'function'		=> 'tab',	
			'capability'	=> $capability,	
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/server-status.php',
			'summary'		=> '系统信息扩展让你在后台就能够快速实时查看当前的系统状态，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-basic-service-status/" target="_blank">系统信息扩展</a>。'
		];

		if(!is_multisite() || !is_network_admin()){
			$subs['wpjam-crons']		= [
				'menu_title'	=> '定时作业',		
				'function'		=> 'tab',
				'summary'		=> '定时作业扩展让你可以可视化管理 WordPress 的定时作业，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-basic-cron-jobs/" target="_blank">定时作业扩展</a>',
				'tabs'			=> [
					'all'	=> [
						'title'		=> '定时作业',	
						'function'	=> 'list',
						'tab_file'	=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/wpjam-crons.php',
					]
				]
			];
			
			$subs['dashicons']		= [
				'menu_title'	=> 'Dashicons',
				'page_file'		=> WPJAM_BASIC_PLUGIN_DIR .'admin/pages/dashicons.php',	
				'summary'		=> 'Dashicons 功能列出所有的 Dashicons 以及每个 Dashicon 的名称和 HTML 代码，详细介绍请查看：<a href="https://blog.wpjam.com/m/wpjam-basic-dashicons/" target="_blank">Dashicons</a>，在 WordPress 后台<a href="https://blog.wpjam.com/m/using-dashicons-in-wordpress-admin/" target="_blank">如何使用 Dashicons</a>。'
			];
		}

		$subs['wpjam-extends']	= [
			'menu_title'	=> '扩展管理',
			'function'		=> 'option',
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-extends.php'
		];

		if($verified !== 'verified'){
			$subs['wpjam-basic-topics']	= [
				'menu_title'	=> '讨论组',
				'function'		=> 'tab',
				'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-topics.php'
			];
		}
	}

	if($verified !== 'verified'){
		$subs['wpjam-about']	= [
			'menu_title'	=> '关于WPJAM',	
			'function'		=> 'wpjam_basic_about_page',	
			'page_file'		=> WPJAM_BASIC_PLUGIN_DIR.'admin/pages/wpjam-about.php'
		];
	}	

	$wpjam_pages['wpjam-basic']	= [
		'menu_title'	=> 'WPJAM',	
		'icon'			=> 'dashicons-performance',
		'position'		=> '58.99',	
		'function'		=> 'option',	
		'subs'			=> $subs
	];

	return $wpjam_pages;
}
add_filter('wpjam_pages', 'wpjam_basic_admin_pages');
add_filter('wpjam_network_pages', 'wpjam_basic_admin_pages');

add_action('admin_menu', function(){
	global $menu, $submenu;
	$menu['58.88']	= ['',	'read',	'separator'.'58.88', '', 'wp-menu-separator'];
}); 

add_action('wpjam_builtin_page_load', function($screen_base){
	if(in_array($screen_base, ['dashboard', 'dashboard-network', 'dashboard-user'])){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/dashboard.php';
	}elseif($screen_base == 'edit'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/post-list.php';
	}elseif($screen_base == 'post'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/post.php';
	}elseif($screen_base == 'edit-tags' || $screen_base == 'term'){
		include WPJAM_BASIC_PLUGIN_DIR.'admin/hooks/term-list.php';
	}
}, 11);

function wpjam_add_basic_sub_page($menu_slug, $args=[]){
	global $wpjam_basic_sub_pages;

	if(!is_array($wpjam_basic_sub_pages)){
		$wpjam_basic_sub_pages = [];
	}

	$wpjam_basic_sub_pages[$menu_slug]	= $args;
}

