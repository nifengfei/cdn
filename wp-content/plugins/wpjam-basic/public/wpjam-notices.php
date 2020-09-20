<?php
class WPJAM_Notice{
	public static $errors = [];

	public static function add($notice, $user_id=0){
		$notice['user_id']	= $user_id;
		$notice['time']		= $notice['time'] ?? time();
		$notice['key']		= $notice['key'] ?? md5(maybe_serialize($notice));

		$notices	= self::get_notices($user_id);
		$key		= $notice['key'];

		$notices[$key]	= $notice;

		return self::update_notices($notices, $user_id);
	}

	public static function delete($key, $user_id=0){
		$result		= false;
		$notices	= self::get_notices($user_id);

		if(isset($notices[$key])){
			unset($notices[$key]);
			$result	= self::update_notices($notices, $user_id);
		}

		if(!$user_id){
			return self::delete($key, get_current_user_id());
		}else{
			return $result;
		}
	}

	public static function get_notices($user_id=0){
		$notices	= $user_id ? get_user_meta($user_id, 'wpjam_notices', true) : get_option('wpjam_notices');
		
		if($notices){
			$notices = array_filter($notices, function($notice){
				return $notice['time'] > time() - MONTH_IN_SECONDS * 3;
			});
		}

		return $notices ?: [];
	}

	public static function update_notices($notices, $user_id=0){
		if(empty($notices)){
			return $user_id ? delete_user_meta($user_id, 'wpjam_notices') : delete_option('wpjam_notices');
		}else{
			return $user_id ? update_user_meta($user_id, 'wpjam_notices', $notices) : update_option('wpjam_notices', $notices);
		}
	}
}

function wpjam_add_admin_notice($notice, $blog_id=0){
	$switched	= $blog_id ? switch_to_blog($blog_id) : false;
	$result		= WPJAM_Notice::add($notice);
	
	if($switched){
		restore_current_blog();
	}

	return $result;
}

function wpjam_add_user_notice($user_id, $notice){
	$user_id	= $user_id ?: get_current_user_id();

	return WPJAM_Notice::add($notice, $user_id);
}

function wpjam_admin_add_error($message='', $type='success'){
	WPJAM_Notice::$errors[]	= compact('message','type');
}

if(is_admin()){
	add_action('admin_notices', function(){
		if(WPJAM_Notice::$errors){
			foreach (WPJAM_Notice::$errors as $error){
				$error	= wp_parse_args($error, ['type'=>'error',	'message'=>'']);

				if($error['message']){
					echo '<div class="notice notice-'.$error['type'].' is-dismissible"><p>'.$error['message'].'</p></div>';
				}
			}
		}

		if($notice_key	= wpjam_get_parameter('notice_key')){
			WPJAM_Notice::delete($notice_key);
		}

		$notices	= WPJAM_Notice::get_notices(get_current_user_id());

		if(current_user_can('manage_options')){
			$notices	= array_merge($notices, WPJAM_Notice::get_notices());
		}

		if(empty($notices)){
			return;
		}

		uasort($notices, function($n, $m){ return $m['time'] <=> $n['time']; });

		$modal_notice	= '';

		foreach ($notices as $notice_key => $notice){
			$notice = wp_parse_args($notice, [
				'type'		=> 'info',
				'class'		=> 'is-dismissible',
				'admin_url'	=> '',
				'notice'	=> '',
				'title'		=> '',
				'modal'		=> 0,
			]);

			$admin_notice	= $notice['notice'];

			if($notice['admin_url']){
				$admin_notice	.= $notice['modal'] ? "\n\n" : ' ';
				$admin_notice	.= '<a style="text-decoration:none;" href="'.add_query_arg(compact('notice_key'), home_url($notice['admin_url'])).'">点击查看<span class="dashicons dashicons-arrow-right-alt"></span></a>';
			}

			$admin_notice	= wpautop($admin_notice).wpjam_get_ajax_button([
				'tag'			=>'span',
				'action'		=>'delete_notice', 
				'class'			=>'hidden',
				'button_text'	=>'删除',
				'data'			=>compact('notice_key'),
				'direct'		=>true
			]);

			if($notice['modal']){
				if($modal_notice){
					continue;	// 弹窗每次只显示一条
				}

				$modal_notice	= wpjam_json_encode($admin_notice);
				$modal_title	= $notice['title'] ?: '消息';
			}else{
				echo '<div class="notice notice-'.$notice['type'].' '.$notice['class'].'">'.$admin_notice.'</div>';
			}
		}

		if($modal_notice){
			?>
			<script type="text/javascript">
			jQuery(function($){
				$('#tb_modal').html('<?php echo $modal_notice; ?>');
				tb_show('<?php echo esc_js($modal_title); ?>', "#TB_inline?inlineId=tb_modal&height=200");
				tb_position();
			});
			</script>
			<?php
		}
	});

	add_action('wpjam_page_action', function($action){
		if($action == 'delete_notice'){
			if($notice_key = wpjam_get_data_parameter('notice_key')){
				WPJAM_Notice::delete($notice_key);
				wpjam_send_json();
			}else{
				wpjam_send_json([
					'errcode'	=> 'invalid_notice_key',
					'errmsg'	=> '无效消息'
				]);
			}
		}
	});
}