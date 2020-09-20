<?php
add_filter('wpjam_bind_tabs', function($tabs){
	$tabs	= [];

	$login_actions	= wpjam_get_login_actions('bind');

	foreach ($login_actions as $login_key => $login_action) {
		$tabs[$login_key]	= ['title'=>$login_action['title'],	'function'=>'wpjam_'.$login_key.'_bind_page'];
	}

	return $tabs;
});

function wpjam_weixin_bind_page(){
	echo '<h2>微信公众号</h2>';

	$user_id	= get_current_user_id();

	$openid			= wpjam_get_user_weixin_openid($user_id, 'weixin');
	$weixin_user	= []; 
	
	if($openid){
		$weixin_user	= wpjam_get_weixin_user($openid);

		if(!$weixin_user){
			wpjam_admin_add_error('绑定错误，请重新改绑定！', 'error');
		}
	}

	if($weixin_user){
		echo '<div class="card">';
		echo '
		<p>你绑定的微信账号是：</p>
		<p>
		昵称：'.$weixin_user['nickname'].'<br />
		地区：'.$weixin_user['province'].' '.$weixin_user['city'].
		'</p>
		<p>
		<img src="'.str_replace('/132', '/0', $weixin_user['headimgurl']).'" width="160" />
		</p>';

		echo '<p>';

		wpjam_ajax_button([
			'action'		=> 'unbind',
			'button_text'	=> '解除绑定',
			'direct'		=> true,
			'confirm'		=> true
		]);

		echo '</p>';

		echo '</div>';
	}else{
		$fields = [
			'qrcode'	=> ['title'=>'二维码',	'type'=>'view'],
			// 'code'		=> array('title'=>'验证码',	'type'=>'number',	'class'=>'',	'description'=>'验证码10分钟内有效！'),
			'bind'		=> ['title'=>'操作说明',	'type'=>'view',	'value'=>'扫描上面的二维码，刷新即可！'],
			'scene'		=> ['title'=>'scene',	'type'=>'hidden'],
		];

		$key			= md5('weixin_bind_'.$user_id);
		$wpjam_qrcode	= wpjam_create_weixin_qrcode($key, $user_id);

		if(is_wp_error($wpjam_qrcode)){
			wpjam_admin_add_error('二维码创建失败，请刷新重试！', 'error');
		}else{
			$qrcode = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$wpjam_qrcode['ticket'];
			$fields['qrcode']['value']	= '<img 2x" src="'.$qrcode.'" style="max-width:215px;" />';
			$fields['scene']['value']	= $wpjam_qrcode['scene'];
		}

		?>
		<p>使用微信扫一扫，绑定账号之后就可以直接微信扫码登录了。</p>
		<div class="card">
		<style type="text/css">
		.form-table th {width:60px; }
		</style>
		<?php

		wpjam_ajax_form([
			'action'		=> 'fresh',
			'fields'		=> $fields,
			'submit_text'	=> '刷新'
		]);

		echo '</div>';
	}

	?>

	<style type="text/css">
		.form-table th{width: 80px;}
	</style>

	<script type="text/javascript">
	jQuery(function($){
		$('body').on('page_action_success', function(e, response){
			window.location.reload();
		});
	});
	</script>

	<?php
}

function wpjam_weapp_bind_page(){
	echo '<h2>微信小程序</h2>';

	$user_id	= get_current_user_id();

	$openid		= wpjam_get_user_weapp_openid($user_id);
	$weapp_user	= []; 
	
	if($openid){
		$weapp_user	= wpjam_get_weapp_user($openid);

		if(!$weapp_user){
			wpjam_admin_add_error('绑定错误，请重新改绑定！', 'error');
		}
	}

	if($weapp_user){
		echo '<div class="card">';
		echo '
		<p>你绑定的微信账号是：</p>
		<p>
		昵称：'.$weapp_user['nickname'].'<br />
		地区：'.$weapp_user['province'].' '.$weapp_user['city'].
		'</p>
		<p>
		<img src="'.str_replace('/132', '/0', $weapp_user['avatarurl']).'" width="160" />
		</p>';

		echo '<p>';

		wpjam_ajax_button([
			'action'		=> 'unbind',
			'button_text'	=> '解除绑定',
			'direct'		=> true,
			'confirm'		=> true
		]);

		echo '</p>';

		echo '</div>';
	}else{
		$fields = [
			'qrcode'	=> ['title'=>'二维码',	'type'=>'view'],
			// 'code'		=> array('title'=>'验证码',	'type'=>'number',	'class'=>'',	'description'=>'验证码10分钟内有效！'),
			'bind'		=> ['title'=>'操作说明',	'type'=>'view',	'value'=>'扫描上面的二维码，刷新即可！'],
			'scene'		=> ['title'=>'scene',	'type'=>'hidden'],
		];

		$key			= md5('weapp_bind_1'.$user_id);
		$wpjam_qrcode	= wpjam_create_weapp_qrcode($key, $user_id);

		if(is_wp_error($wpjam_qrcode)){
			wpjam_admin_add_error('二维码创建失败，请刷新重试！', 'error');
		}else{
			$fields['qrcode']['value']	= '<img " src="'.$wpjam_qrcode['qrcode_url'].'" style="max-width:215px;" />';
			$fields['scene']['value']	= $wpjam_qrcode['scene'];
		}

		?>
		<p>使用微信扫一扫，绑定账号之后就可以直接微信扫码登录了。</p>
		<div class="card">
		<style type="text/css">
		.form-table th {width:60px; }
		</style>
		<?php

		wpjam_ajax_form([
			'action'		=> 'fresh',
			'fields'		=> $fields,
			'submit_text'	=> '刷新'
		]);

		echo '</div>';
	}

	?>

	<style type="text/css">
		.form-table th{width: 80px;}
	</style>

	<script type="text/javascript">
	jQuery(function($){
		$('body').on('page_action_success', function(e, response){
			window.location.reload();
		});
	});
	</script>

	<?php
}


function wpjam_phone_bind_page(){
	echo '<h2>手机号码绑定</h2>';

	$user_id	= get_current_user_id();

	$phone		= wpjam_get_user_phone();

	if($phone){
		echo '<div class="card">';
		echo '
		<p>你绑定的手机号码是：<br />
		<strong>'.$phone.'</strong></p>';

		echo '<p>';

		wpjam_ajax_button([
			'action'		=> 'unbind',
			'button_text'	=> '解除绑定',
			'direct'		=> true,
			'confirm'		=> true
		]);

		echo '</p>';

		echo '</div>';
		
	}else{
		$fields = array(
			'phone'		=> array('title'=>'手机号码',	'type'=>'number',	'class'=>'all-options',	'description'=>wpjam_get_ajax_button(['action'=>'code', 'class'=>'button', 'button_text'=>'获取验证码', 'direct'=>true,	'data'=>['phone'=>'']])),
			'code'		=> array('title'=>'验证码',	'type'=>'text',		'class'=>'',	'description'=>'验证码10分钟内有效！'),
		);

		?>
		<style type="text/css">
		.form-table th {width:60px; }
		</style>
		<?php

		wpjam_ajax_form([
			'action'		=> 'bind',
			'fields'		=> $fields,
			'submit_text'	=> '绑定'
		]);
	}

	?>

	<style type="text/css">
		.form-table th{width: 80px;}
	</style>

	<script type="text/javascript">
	jQuery(function($){
		$('#wpjam_button_code').on('click', function(){
			$(this).data('data','phone='+$('input#phone').val());
		});

		$('body').on('page_action_success', function(e, response){
			// console.log(response);
			window.location.reload();
		});
	});
	</script>

	<?php
}

function wpjam_bind_ajax_response(){
	global $current_tab;

	$user_id	= get_current_user_id();
	$action		= $_POST['page_action'];

	if($action == 'fresh'){
		$user_id	= get_current_user_id();
		$openid 	= wpjam_get_user_openid($user_id, $current_tab);	

		if(!$openid){
			wpjam_send_json([
				'errcode'	=> 'scan_fail',
				'errmsg'	=> '请先扫描，再点击刷新。'
			]);
		}

	}elseif($action == 'unbind'){
		if($current_tab == 'weixin'){
			wpjam_weixin_unbind($user_id);;	
		}elseif($current_tab == 'weapp'){
			wpjam_weapp_unbind($user_id);
		}
	}elseif($action == 'code'){
		$data	= wp_parse_args($_POST['data']);
		$phone	= $data['phone'] ?? '';

		$response	= wpjam_send_sms(trim($phone));

		if(is_wp_error($response)){
			wpjam_send_json($response);
		}
	}
	
	wpjam_send_json();
}