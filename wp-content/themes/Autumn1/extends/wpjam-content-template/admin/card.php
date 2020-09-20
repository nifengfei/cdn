<?php
function wpjam_card_page(){
	$post_id	= wpjam_get_data_parameter('post_id');

	if($post_id){
		$post			= get_post($post_id);
		$post_title		= $post->post_title;
		$post_excerpt	= $post->post_excerpt;
		$post_password	= $post->post_password;
		$post_content	= $post->post_content;

		$content		= maybe_unserialize($post_content);
		$card_type		= $content['card_type'] ?? 1;
		$thumbnail		= $content['thumbnail'] ?? '';
		$price			= $content['price'] ?? '';
		$link			= $content['link'] ?? '';
		$weapp			= $content['weapp'] ?? [];
	}else{
		$post_title		= $post_excerpt = $post_password = $thumbnail = $price = $link = '';
		$card_type		= 1;
		$weapp			= [];	
	}

	$card_types	= [
		1=>'小图模式：图片显示在左侧，尺寸为200x200，其他所有信息都会显示。',
		2=>'大图模式：图片全屏显示，高度自适应，然后只显示标题。'
	];

	if(defined('WEAPP_PLUGIN_DIR')){
		if($weapps	= wpjam_get_setting('wpjam-content-template', 'weapps')){
			$weapps	= wp_list_pluck($weapps, 'name', 'appid');
		}else{
			$weapps	= [];
		}

		$weapps		= ['webview'=>'跳转网页','weapp'=>'本小程序'] + $weapps;
	}else{
		$weapps	= [];
	}

	$fields		= [
		'card_type'		=> ['title'=>'卡片样式',		'type'=>'radio',	'value'=>$card_type,	'options'=>$card_types,	'sep'=>'<br /><br />'],
		'card_content'	=> ['title'=>'卡片内容',		'type'=>'fieldset',	'fields'=>[
			'thumbnail'		=> ['title'=>'图片',		'type'=>'img',		'value'=>$thumbnail,	'item_type'=>'url'],
			'post_title'	=> ['title'=>'标题',		'type'=>'text',		'value'=>$post_title],
			'post_excerpt'	=> ['title'=>'简介',		'type'=>'textarea',	'value'=>$post_excerpt,	'class'=>'',	'rows'=>4],
			'price'			=> ['title'=>'价格',		'type'=>'text',		'value'=>$price,		'class'=>'',	'description'=>'输入价格会显示「去选购」按钮'],
			// 'post_password'	=> ['title'=>'密码',		'type'=>'text',		'value'=>$post_password,'class'=>'',	'description'=>'设置了密码保护，则前端必须输入密码才可查看'],
		]],
		'card_link'		=> ['title'=>'卡片跳转',		'type'=>'fieldset',	'fields'=>[
			'link'			=> ['title'=>'网页链接',	'type'=>'url',		'value'=>$link],
			'weapp'			=> ['title'=>'',		'type'=>'fieldset',	'value'=>$weapp,	'fieldset_type'=>'array',	'fields'=>[
				'appid'	=> ['title'=>'小程序',		'type'=>'select',	'options'=>$weapps],
				'path'	=> ['title'=>'',			'type'=>'text',		'placeholder'=>'请输入小程序路径，不填则跳转首页'],
			]],
		]],	
		'post_id'		=> ['title'=>'',		'type'=>'hidden',	'value'=>$post_id],
	];

	if(!defined('WEAPP_PLUGIN_DIR')){
		unset($fields['card_link']['fields']['weapp']);
	}

	$action_text	= $post_id ? '编辑' : '新建';

	echo '<h1>'.$action_text.'卡片</h1>';	

	wpjam_ajax_form([
		'fields'		=> $fields, 
		'action'		=> 'save',
		'submit_text'	=> $action_text
	]);
}

function wpjam_card_ajax_response(){
	global $plugin_page; 

	$action	= $_POST['page_action'];

	check_ajax_referer($plugin_page.'-'.$action);

	$post_id	= wpjam_get_data_parameter('post_id');
	$data		= wp_parse_args($_POST['data']);

	if($action == 'save'){
		$card_type		= $data['card_type'] ?? 0;

		$post_title		= $data['post_title'] ?? '';
		$post_excerpt	= $data['post_excerpt'] ?? '';
		$post_password	= $data['post_password'] ?? '';
		$thumbnail		= $data['thumbnail'] ?? '';
		
		$price			= $data['price'] ?? '';
		$link			= $data['link'] ?? 0;
		$weapp			= $data['weapp'] ?? 0;

		$post_content	= maybe_serialize(compact('card_type', 'thumbnail', 'price', 'link', 'weapp'));

		if($post_id){
			$post	= get_post($post_id);
		
			$post_status	= $post->post_status;
			if($thumbnail && $post_status != 'publish'){
				$post_status	= 'publish';
			}

			$post_id		= WPJAM_Post::update($post_id, compact('post_title', 'post_excerpt', 'post_content', 'post_status',	'post_password'));
			$is_add			= false;
		}else{
			$post_type		= 'template';
			$post_status	= $thumbnail ? 'publish' : 'draft';
			$post_id		= WPJAM_Post::insert(compact('post_type', 'post_title', 'post_excerpt', 'post_content', 'post_status',	'post_password'));
			$is_add			= true;
		}

		if(is_wp_error($post_id)){
			wpjam_send_json($post_id);
		}

		update_post_meta($post_id, '_template_type', 'card');

		wpjam_send_json(compact('post_id', 'is_add'));
	}
}

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('change', '#tr_card_type input[type="radio"]', function(){
			var selected = $('#tr_card_type input[type="radio"]:checked').val();

			if(selected == 1){
				$('#div_post_excerpt').show();
				$('#div_price').show();
			}else{
				$('#div_post_excerpt').hide();
				$('#div_price').hide();
			}
		});

		$('body').on('change', '#appid', function(){
			if($(this).val() == 'webview'){
				$('#div_path').hide();
			}else{
				$('#div_path').show();
			}
		});

		$('#tr_card_type input[type="radio"]').change();
		$('#appid').change();

		$('body').on('page_action_success', function(e, response){
			if(response.is_add){
				window.history.replaceState(null, null, window.location.href + '&post_id=' + response.post_id);

				$('.wrap h1').text($('.wrap h1').text().replace('新建', '编辑'));
				$('title').text($("title").text().replace('新建', '编辑'));

				$('input[type="submit"]').val('编辑');
				$('input#post_id').val(response.post_id);

				$('li#menu-posts-template ul li').removeClass('current');
				$('li#menu-posts-template ul li.wp-first-item').addClass('current');
			}
		});
	});
	</script>
	<style type="text/css">
	#div_path, #div_appid{display: inline-block; margin-right: 6px;}
	</style>
	<?php
});