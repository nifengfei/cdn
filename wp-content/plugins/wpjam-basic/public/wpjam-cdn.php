<?php
class WPJAM_CDN{
	protected static $cdns	= [];

	public static function register($cdn, $args=[]){
		self::$cdns[$cdn]	= $args;
	}

	public static function get_all(){
		return self::$cdns;
	}

	public static function load($current){
		if(empty($current)){
			return false;
		}

		if(!isset(self::$cdns[$current])){
			return false;
		}

		$cdn_file	= self::$cdns[$current]['file'] ?? '';

		if($cdn_file && file_exists($cdn_file)){
			include($cdn_file);
		}

		return $current;
	}

	public static function host_replace($html, $to_cdn=true){
		$local_hosts	= [];

		if($to_cdn){
			$local_hosts[]	= str_replace('https://', 'http://', LOCAL_HOST);
			$local_hosts[]	= str_replace('http://', 'https://', LOCAL_HOST);

			if(strpos(CDN_HOST, 'http://') === 0){
				$local_hosts[]	= str_replace('http://', 'https://', CDN_HOST);
			}
		}else{
			if(strpos(LOCAL_HOST, 'https://') !== false){
				$local_hosts[]	= str_replace('https://', 'http://', LOCAL_HOST);
			}else{
				$local_hosts[]	= str_replace('http://', 'https://', LOCAL_HOST);
			}
		}

		$local_hosts	= apply_filters('wpjam_cdn_local_hosts', $local_hosts);
		$local_hosts	= array_unique($local_hosts);
		$local_hosts	= array_map('untrailingslashit', $local_hosts);

		if($to_cdn){
			return str_replace($local_hosts, CDN_HOST, $html);
		}else{
			return str_replace($local_hosts, LOCAL_HOST, $html);
		}
	}

	public static function html_replace($html, $dirs, $exts){
		if($exts){
			$html	= self::host_replace($html, false);

			if($dirs && !is_array($dirs)){
				$dirs	= explode('|', $dirs);
			}

			if(!is_array($exts)){
				$exts	= explode('|', $exts);
			}

			$dirs	= array_unique(array_filter(array_map('trim', $dirs)));
			$exts	= array_unique(array_filter(array_map('trim', $exts)));

			if(is_login()){
				$exts	= array_diff($exts, ['js','css']);
			}

			$exts	= implode('|', $exts);
			$dirs	= implode('|', $dirs);

			if($dirs){
				$dirs	= str_replace(['-','/'],['\-','\/'], $dirs);
				$regex	= '/'.str_replace('/','\/',LOCAL_HOST).'\/(('.$dirs.')\/[^\s\?\\\'\"\;\>\<]{1,}.('.$exts.'))([\"\\\'\)\s\]\?]{1})/';
				$html	= preg_replace($regex, CDN_HOST.'/$1$4', $html);
			}else{
				$regex	= '/'.str_replace('/','\/',LOCAL_HOST).'\/([^\s\?\\\'\"\;\>\<]{1,}.('.$exts.'))([\"\\\'\)\s\]\?]{1})/';
				$html	= preg_replace($regex, CDN_HOST.'/$1$3', $html);
			}
		}

		return $html;
	}

	public static function content_images($content, $max_width=0){
		if(false === strpos( $content, '<img')){
			return $content;
		}

		$content	= self::host_replace($content, false);

		if(!preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $content, $matches)){
			return $content;
		}

		$search		= $replace = [];

		foreach ($matches[0] as $i => $img_tag){
		 	$img_url	= $matches[1][$i];

		 	if(empty($img_url)){
		 		continue;
		 	}
		 	
		 	if(wpjam_is_remote_image($img_url)){
		 		$new_img_url	= apply_filters('wpjam_content_remote_image', $img_url);

		 		if($img_url == $new_img_url){
		 			continue;
		 		}else{
		 			$img_url = $new_img_url;
		 		}
			}

			$size	= ['width'=>0,	'height'=>0,	'content'=>true];

			if(preg_match_all('/(width|height)=[\'"]([0-9]+)[\'"]/i', $img_tag, $hw_matches)){
				$hw_arr	= array_flip($hw_matches[1]);
				$size	= array_merge($size, array_combine($hw_matches[1], $hw_matches[2]));
			}

			$img_serach	= $img_replace	= [];

			if($max_width) {
				if($size['width'] >= $max_width){
					if($size['height']){
						$size['height']	= intval(($max_width / $size['width']) * $size['height']);

						$img_serach[]	= $hw_matches[0][$hw_arr['height']];
						$img_replace[]	= 'height="'.$size['height'].'"';
					}
					
					$size['width']	= $max_width;

					$img_serach[]	= $hw_matches[0][$hw_arr['width']];
					$img_replace[]	= 'width="'.$size['width'].'"';
				}elseif($size['width'] == 0){
					if($size['height'] == 0){
						$size['width']	= $max_width;
					}
				}

				if(function_exists('wp_lazy_loading_enabled')){
					$add_loading_attr	= wp_lazy_loading_enabled('img', current_filter());

					if($add_loading_attr && false === strpos($img_tag, ' loading=')) {
						$img_serach[]	= '<img';
						$img_replace[]	= '<img loading="lazy"';
					}
				}
			}

			$size['width']	= $size['width']*2;
			$size['height']	= $size['height']*2;

			$img_serach[]	= $matches[1][$i];
			$img_replace[]	= wpjam_get_thumbnail($img_url, $size);
			
			$search[]		= $img_tag;
			$replace[]		= str_replace($img_serach, $img_replace, $img_tag);
		}

		if(!$search){
			return $content;
		}

		return str_replace($search, $replace, $content);
	}
}

// 获取 CDN 设置
function wpjam_cdn_get_setting($setting_name){
	return wpjam_get_setting('wpjam-cdn', $setting_name);
}

// 获取所有注册的 CDN 服务
function wpjam_get_cdns(){
	return WPJAM_CDN::get_all();
}

//注册 CDN 服务
function wpjam_register_cdn($key, $args){
	WPJAM_CDN::register($key, $args);
}

function wpjam_is_remote_image($img_url){
	$status = strpos($img_url, home_url()) === false;
	
	return apply_filters('wpjam_is_remote_image', $status, $img_url);
}

foreach (['aliyun_oss'=>'阿里云OSS', 'qcloud_cos'=>'腾讯云COS', 'ucloud'=>'UCloud UFile', 'qiniu'=>'七牛云存储'] as $cdn_key => $cdn_title) {
	wpjam_register_cdn($cdn_key, [
		'title'	=> $cdn_title, 
		'file'	=> WPJAM_BASIC_PLUGIN_DIR.'cdn/'.$cdn_key.'.php',
	]);
}

add_action('plugins_loaded', function(){
	$current_cdn	= wpjam_cdn_get_setting('cdn_name');
	$current_cdn	= WPJAM_CDN::load($current_cdn);

	if(empty($current_cdn)){
		return;
	}

	define('CDN_NAME',		$current_cdn);
	define('LOCAL_HOST',	untrailingslashit(wpjam_cdn_get_setting('local') ? set_url_scheme(wpjam_cdn_get_setting('local')): site_url()));
	define('CDN_HOST',		untrailingslashit(wpjam_cdn_get_setting('host') ?: site_url()));

	add_filter('wpjam_cdn_local_hosts', function($local_hosts){
		$locals	= wpjam_cdn_get_setting('locals') ?: [];

		return array_merge($local_hosts, $locals);
	});

	// 不用生成 -150x150.png 这类的图片
	add_filter('intermediate_image_sizes_advanced', function($sizes){
		if(isset($sizes['full'])){
			return ['full'=>$sizes['full']];
		}else{
			return [];
		}
	});

	add_filter('image_size_names_choose', function($sizes){
		$_sizes	= $sizes;

		$sizes	= [];
		$sizes['full']	= $_sizes['full'];
		unset($_sizes['full']);

		foreach(['large', 'medium', 'thumbnail'] as $key){
			if(get_option($key.'_size_w') || get_option($key.'_size_h')){
				$sizes[$key]	= $_sizes[$key];
			}else{
				unset($_sizes[$key]);
			}
		}

		if($_sizes){
			foreach ($_sizes as $key => $value) {
				$sizes[$key]	= $value;
			}
		}

		return $sizes;
	});

	add_filter('wpjam_thumbnail', ['WPJAM_CDN','host_replace'], 1);

	add_filter('upload_dir', function($uploads){
		$uploads['url']		= WPJAM_CDN::host_replace($uploads['url']);
		$uploads['baseurl']	= WPJAM_CDN::host_replace($uploads['baseurl']);
		return $uploads;
	});

	add_filter('the_content', function($content){
		if($max_width = intval(apply_filters('wpjam_content_image_width', wpjam_cdn_get_setting('width')))){
			if(has_filter('the_content', 'wp_filter_content_tags')){
				add_filter('wp_img_tag_add_srcset_and_sizes_attr', '__return_false');
				remove_filter('the_content', 'wp_filter_content_tags');
			}else{
				remove_filter('the_content', 'wp_make_content_images_responsive');
			}
		}

		if(doing_filter('get_the_excerpt')){
			return $content;
		}else{
			return WPJAM_CDN::content_images($content, $max_width);
		}
	}, 5);

	add_filter('image_downsize', function($out, $id, $size){
		if(!wp_attachment_is_image($id)){	
			return false;
		}

		$meta		= wp_get_attachment_metadata($id);
		$img_url	= wp_get_attachment_url($id);	

		$size		= wpjam_parse_size($size);

		if($size['crop']){
			$size['width']	= min($size['width'],  $meta['width']);
			$size['height']	= min($size['height'],  $meta['height']);
		}else{
			list($width, $height)	= wp_constrain_dimensions($meta['width'], $meta['height'], $size['width'], $size['height']);

			$size['width']	= $width;
			$size['height']	= $height;
		}

		if($size['width'] < $meta['width'] || $size['height'] <  $meta['height']){
			$img_url	= wpjam_get_thumbnail($img_url, $size);
		}else{
			$img_url	= wpjam_get_thumbnail($img_url);
		}

		return [$img_url, $size['width'], $size['height'], 1];
	},10 ,3);

	if(!is_admin()){
		add_filter('wpjam_html', function($html){
			$dirs	= wpjam_cdn_get_setting('dirs') ?: [];
			$exts	= wpjam_cdn_get_setting('exts') ?: [];

			return WPJAM_CDN::html_replace($html, $dirs, $exts);
		},9);

		add_filter('wp_resource_hints', function($urls, $relation_type){
			return $relation_type == 'dns-prefetch' ? $urls+[CDN_HOST] : $urls;
		}, 10, 2);
	}

	add_filter('wpjam_is_remote_image', function($status, $img_url){
		return strpos($img_url, CDN_HOST) === false && strpos($img_url, LOCAL_HOST) === false;
	}, 1, 2);

	if(wpjam_cdn_get_setting('remote') == 'rewrite'){
		include WPJAM_BASIC_PLUGIN_DIR.'cdn/remote.php';
	}
}, 99);

