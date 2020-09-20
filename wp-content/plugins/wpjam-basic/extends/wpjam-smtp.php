<?php
/*
Plugin Name: SMTP 发信
Plugin URI: https://blog.wpjam.com/project/wpjam-basic/
Description: 简单配置就能让 WordPress 使用 SMTP 发送邮件。
Version: 1.0
*/
function wpjam_smtp_get_setting($setting_name){
	if(wpjam_get_option('wpjam-smtp')){
		return wpjam_get_setting('wpjam-smtp', $setting_name);
	}else{
		return wpjam_basic_get_setting('smtp_'.$setting_name);
	}
}

add_action('phpmailer_init',function ($phpmailer) {
	$phpmailer->isSMTP(); 

	// $phpmailer->SMTPDebug	= 1;

	$phpmailer->SMTPAuth	= true;
	$phpmailer->SMTPSecure	= wpjam_smtp_get_setting('ssl');
	$phpmailer->Host		= wpjam_smtp_get_setting('host'); 
	$phpmailer->Port		= wpjam_smtp_get_setting('port');
	$phpmailer->Username	= wpjam_smtp_get_setting('user');
	$phpmailer->Password	= wpjam_smtp_get_setting('pass');

	if($smtp_reply_to_mail	= wpjam_smtp_get_setting('reply_to_mail')){
		$name	= wpjam_smtp_get_setting('mail_from_name') ?: '';
		$phpmailer->AddReplyTo($smtp_reply_to_mail, $name);
	}
});

add_filter('wp_mail_from', function(){
	return wpjam_smtp_get_setting('user');
});

add_filter('wp_mail_from_name', function($name){
	return wpjam_smtp_get_setting('mail_from_name') ?: $name;
});

if(is_admin()){
	wpjam_add_basic_sub_page('wpjam-smtp', [
		'menu_title'	=> '发信设置',
		'page_title'	=> 'SMTP邮件服务',
		'function'		=> 'tab',
		'tabs'			=> [
			'smtp'	=> ['title'=>'发信设置',	'function'=>'option'],
			'send'	=> ['title'=>'发送测试',	'function'=>'wpjam_smtp_send_page'],
		],
		'summary'		=> 'SMTP 邮件服务扩展让你可以使用第三方邮箱的 SMTP 服务来发邮件，详细介绍请点击：<a href="https://blog.wpjam.com/m/wpjam-smtp/" target="_blank">SMTP 邮件服务扩展</a>，点击这里查看：<a target="_blank" href="http://blog.wpjam.com/m/gmail-qmail-163mail-imap-smtp-pop3/" target="_blank">常用邮箱的 SMTP 设置</a>。'
	]);

	add_action('wpjam_plugin_page_load', function($plugin_page, $current_tab){
		if($plugin_page != 'wpjam-smtp' || !empty($current_tab)){
			return;
		}

		if(!wpjam_get_option('wpjam-smtp')){
			$smtp_value	= [];

			foreach (['smtp_host', 'smtp_ssl', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_mail_from_name', 'smtp_reply_to_mail'] as $setting_name){
				if($setting_value	= wpjam_basic_get_setting($setting_name)){
					$smtp_value[ltrim($setting_name, 'smtp_')]	= $setting_value;
				}

				wpjam_basic_delete_setting($setting_name);
			}

			if($smtp_value){
				update_option('wpjam-smtp', $smtp_value);
			}
		}

		add_filter('wpjam_smtp_setting', function(){
			$fields = [
				'smtp_setting'		=> ['title'=>'SMTP 设置',	'type'=>'fieldset','fields'=>[
					'host'	=> ['title'=>'地址',		'type'=>'text',		'class'=>'all-options',	'value'=>'smtp.qq.com'],
					'ssl'	=> ['title'=>'发送协议',	'type'=>'text',		'class'=>'',			'value'=>'ssl'],
					'port'	=> ['title'=>'SSL端口',	'type'=>'number',	'class'=>'',			'value'=>'465'],
					'user'	=> ['title'=>'邮箱账号',	'type'=>'email',	'class'=>'all-options'],
					'pass'	=> ['title'=>'邮箱密码',	'type'=>'password',	'class'=>'all-options'],
				]],
				'mail_from_name'	=> ['title'=>'发送者姓名',	'type'=>'text',	'class'=>''],
				'reply_to_mail'		=> ['title'=>'回复地址',		'type'=>'email','class'=>'all-options',	'description'=>'不填则用户回复使用SMTP设置中的邮箱账号']
			];

			return compact('fields');
		});

		add_action('wpjam_page_action', function($action){
			if($action == 'submit'){
				$to			= wpjam_get_data_parameter('to');
				$subject	= wpjam_get_data_parameter('subject');
				$message	= wpjam_get_data_parameter('message');

				if(wp_mail($to, $subject, $message)){
					wpjam_send_json();
				}
			}
		});

		add_action('wp_mail_failed', function ($mail_failed){
			wpjam_send_json($mail_failed);
		});

		function wpjam_smtp_send_page(){
			$fields = array(
				'to'		=> array('title'=>'收件人',	'type'=>'email',	'required'),
				'subject'	=> array('title'=>'主题',	'type'=>'text',		'required'),
				'message'	=> array('title'=>'内容',	'type'=>'textarea',	'style'=>'max-width:640px;',	'rows'=>8,	'required'),
			);

			wpjam_ajax_form([
				'fields'		=> $fields, 
				'action'		=> 'submit', 
				'submit_text'	=> '发送'
			]);
		}
	}, 10, 2);
}