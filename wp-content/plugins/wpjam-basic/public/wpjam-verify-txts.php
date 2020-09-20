<?php
class WPJAM_VerifyTXT{
	private static $verify_txts	= [];

	public static function register($key, $args){
		if(isset(self::$verify_txts[$key])){
			return new WP_Error('verify_txt_registered', '该验证txt文件已被注册');
		}

		self::$verify_txts[$key]	= $args;

		return self::$verify_txts;
	}

	public static function get_name($key){
		if(isset(self::$verify_txts[$key])){
			$value	= wpjam_get_setting('wpjam_verify_txts', $key);

			if($value){
				return $value['name'] ?? '';
			}
		}

		return '';
	}

	public static function get_value($key){
		if(isset(self::$verify_txts[$key])){
			$value	= wpjam_get_setting('wpjam_verify_txts', $key);

			if($value){
				return $value['value'] ?? '';
			}
		}

		return '';
	}

	public static function set($key, $name, $value){
		if(isset(self::$verify_txts[$key])){
			wpjam_update_setting('wpjam_verify_txts', $key, compact('name', 'value'));
			return true;
		}else{
			return false;
		}
	}

	public static function get_value_by_name($name){
		if($values = wpjam_get_option('wpjam_verify_txts')){
			$name	= str_replace('.txt', '', $name).'.txt';
			foreach ($values as $key => $value) {
				if($value['name'] == $name){
					return $value['value'];
				}
			}
		}

		return '';
	}
}

add_filter('root_rewrite_rules', function($root_rewrite){
	global $wp_rewrite;

	if(empty($wp_rewrite->root)){
		$home_path	= parse_url(home_url());

		if(empty($home_path['path']) || '/' == $home_path['path']){
			$root_rewrite	= array_merge(['([^/]+)\.txt?$'=>'index.php?module=txt&action=$matches[1]'], $root_rewrite);
		}
	}
	
	return $root_rewrite;	
});

add_action('wpjam_module', function($module, $action){
	if($module == 'txt'){
		$value	= WPJAM_VerifyTXT::get_value_by_name($action);

		if($value){
			header('Content-Type: text/plain');
			echo $value;
		}else{
			wp_die('错误');
		}

		exit;
	}
}, 10, 2);