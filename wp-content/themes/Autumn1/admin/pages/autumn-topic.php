<?php
add_filter('wpjam_theme_setting', function(){
	$sections	= [
		'topic'			=>[
			'title'		=>'讨论组',
			'summary'	=>'<p>讨论组主页链接是 '.home_url().'/topic ，这里只设置Banner</p>',
			'fields'	=>[
				'add_topic'			=> ['title'=>'开启前端发帖',	'type'=>'checkbox',	'description'=>'开启后，前端可显示发帖按钮（用户中心和讨论组列表页面显示）'],
				'topic_title'		=> ['title'=>'Banner - 标题', 'type'=>'text', 'rows'=>4, 'description'=>'例如：小论坛'],
				'topic_ms'			=> ['title'=>'Banner - 描述', 'type'=>'text', 'rows'=>4, 'description'=>'例如：需要技术支持还是只想打个招呼？ '],
				'topic_bg_img'		=> ['title'=>'Banner - 背景图像', 'type'=>'img',	'item_type'=>'url',	'description'=>'建议尺寸：1920*300'],
			],
		],
	];

	$field_validate 	= function($value){
		include_once WPJAM_TOPIC_PLUGIN_DIR . 'public/upgrade.php';
		wpjam_topic_upgrade(true);

		return $value;
	};

	return compact('sections', 'field_validate');
});