<?php
if(!function_exists('get_post_excerpt')){   
	//获取日志摘要
	function get_post_excerpt($post=null, $excerpt_length=240){
		_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2', 'wpjam_get_post_excerpt');
		return wpjam_get_post_excerpt($post, $excerpt_length);
	}
}

if(!function_exists('str_replace_deep')){
	function str_replace_deep($search, $replace, $value){
		return map_deep($value, function($value) use($search, $replace){
			return str_replace($search, $replace, $value);
		});
	}
}

if(!function_exists('user_can_for_blog')){
	function user_can_for_blog($user, $blog_id, $capability){
		$switched = is_multisite() ? switch_to_blog( $blog_id ) : false;

		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user );
		}

		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		if ( empty( $user ) ) {
			if ( $switched ) {
				restore_current_blog();
			}
			return false;
		}

		$args = array_slice( func_get_args(), 2 );
		$args = array_merge( array( $capability ), $args );

		$can = call_user_func_array( array( $user, 'has_cap' ), $args );

		if ( $switched ) {
			restore_current_blog();
		}

		return $can;
	}
}

if(!function_exists('get_metadata_by_value')){
	function get_metadata_by_value($meta_type, $meta_value, $meta_key=''){
		global $wpdb;

		if(!$meta_type || empty($meta_value)){
			return false;
		}

		$meta_value	= maybe_serialize($meta_value);

		$table = _get_meta_table( $meta_type );
		if(!$table){
			return false;
		}

		if($meta_key){
			$meta	= $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s AND meta_key = %s", $meta_value, $meta_key));
		}else{
			$meta	= $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s", $meta_value));
		}

		if(empty($meta)){
			return false;
		}

		if(isset($meta->meta_value)){
			$meta->meta_value = maybe_unserialize( $meta->meta_value );
		}

		return $meta;
	}
}

if(!function_exists('wp_cache_delete_multi')){
	function wp_cache_delete_multi( $keys, $group = '' ) {
		foreach ($keys as $key) {
			wp_cache_delete($key, $group);
		}

		return true;
	}
}
	
if(!function_exists('wp_cache_get_multi')){	
	function wp_cache_get_multi( $keys, $group = '' ) {

		$datas = [];

		foreach ($keys as $key) {
			$datas[$key] = wp_cache_get($key, $group);
		}

		return $datas;
	}
}

if(!function_exists('wp_cache_get_with_cas')){
	function wp_cache_get_with_cas( $key, $group = '', &$cas_token = null ) {
		return wp_cache_get($key, $group);
	}
}

if(!function_exists('wp_cache_cas')){
	function wp_cache_cas( $cas_token, $key, $data, $group = '', $expire = 0  ) {
		return wp_cache_set($key, $data, $group, $expire);
	}
}

if(!function_exists('get_post_type_support_value')){
	function get_post_type_support_value($post_type, $feature){
		$supports	= get_all_post_type_supports($post_type);

		if($supports && isset($supports[$feature])){
			if(is_array($supports[$feature]) && wp_is_numeric_array($supports[$feature]) && count($supports[$feature]) == 1){
				return current($supports[$feature]);
			}else{
				return $supports[$feature];
			}
		}else{
			return false;
		}
	}
}

class WPJAM_PostType extends WPJAM_Post{}
class WPJAM_Taxonomy extends WPJAM_Term{}

add_filter('rewrite_rules_array', function($rules){
	if(has_filter('wpjam_rewrite_rules')){
		return array_merge(apply_filters('wpjam_rewrite_rules', []), $rules);
	}
	return $rules;
});

function wpjam_get_field_value($field, $args=[]){
	return WPJAM_Field::get_field_value($field, $args);
}

function wpjam_form_field_tmpls($echo=true){}

function wpjam_urlencode_img_cn_name($img_url){
	return $img_url;
}

function wpjam_image_hwstring($size){
	$width	= intval($size['width']);
	$height	= intval($size['height']);
	return image_hwstring($width, $height);
}

function wpjam_get_taxonomy_levels($taxonomy){
	$taxonomy_obj	= get_taxonomy($taxonomy);
	return $taxonomy_obj->levels ?? 0;
}

function wpjam_api_set_response(&$response){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.3');
}

function wpjam_api_signon(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.3');
}

function wpjam_get_post_type_setting($post_type){
	$settings	= get_option('wpjam_post_types') ?: [];
	return $settings[$post_type] ?? [];
}

function wpjam_get_api_setting($json){
	return WPJAM_API::get_api($json);
}

function wpjam_is_json($json=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2', 'wpjam_get_json');

	$wpjam_json = wpjam_get_json();

	if(empty($wpjam_json)){
		return false;
	}

	if($json){
		return $wpjam_json == $json;
	}else{
		return true;
	}
}

function is_wpjam_json($json=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2', 'wpjam_get_json');
	
	return wpjam_is_json($json);
}

function wpjam_has_path($path_type, $page_key){
	$path_obj	= WPJAM_Path::get_instance($page_key);

	return is_null($path_obj) ? false : $path_obj->has($path_type);
}

function wpjam_get_paths_by_post_type($post_type, $path_type){
	return WPJAM_Path::get_by(compact('post_type', 'path_type'));
}

function wpjam_get_paths_by_taxonomy($taxonomy, $path_type){
	return WPJAM_Path::get_by(compact('taxonomy', 'path_type'));
}

function wpjam_generate_path($data){
	$page_key	= $data['page_key'] ?? '';
	$path_type	= $data['path_type'] ?? '';
	$path_type	= $path_type ?: 'weapp'; 	// 历史遗留问题，默认都是 weapp， 非常 ugly 
	return wpjam_get_path($path_type, $page_key, $data);
}

function wpjam_get_paths($path_type=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2', 'wpjam_get_path_objs');
	return wpjam_get_path_objs($path_type);
}

function wpjam_render_path_item($item, $text, $platforms=[]){
	$platform	= wpjam_get_current_platform($platforms);
	$parsed		= wpjam_parse_path_item($item, $platform);
	
	return wpjam_get_path_item_link_tag($parsed, $text);
}

function wpjam_attachment_url_to_postid($url){
	$post_id = wp_cache_get($url, 'attachment_url_to_postid');

	if($post_id === false){
		global $wpdb;

		$upload_dir	= wp_get_upload_dir();
		$path		= str_replace(parse_url($upload_dir['baseurl'], PHP_URL_PATH).'/', '', parse_url($url, PHP_URL_PATH));

		$post_id	= $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s", $path));

		wp_cache_set($url, $post_id, 'attachment_url_to_postid', DAY_IN_SECONDS);
	}

	return (int) apply_filters( 'attachment_url_to_postid', $post_id, $url );
}

// 获取远程图片
function wpjam_get_content_remote_image_url($img_url, $post_id=null){
	return $img_url;
}

function wpjam_image_remote_method($img_url=''){
	return '';
}

function wpjam_get_content_width(){
	return intval(apply_filters('wpjam_content_image_width', wpjam_cdn_get_setting('width')));
}

function wpjam_cdn_replace_local_hosts($html, $to_cdn=true){
	return WPJAM_CDN::host_replace($html, $to_cdn);
}

function wpjam_cdn_content($content){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'WPJAM_CDN::content_images');
	return WPJAM_CDN::content_images($content);
}

function wpjam_content_images($content, $max_width=0){
	return WPJAM_CDN::content_images($content, $max_width);
}

function wpjam_get_content_remote_img_url($img_url, $post_id=0){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_content_remote_image_url');
	return wpjam_get_content_remote_image_url($img_url, $post_id);	
}

function wpjam_get_post_first_image($post=null, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_first_image_url');
	return wpjam_get_post_first_image_url($post=null, $size='full');
}

function wpjam_get_qqv_vid($id_or_url){
	return WPJAM_Utli::get_qqv_id($id_or_url);
}

function wpjam_get_qq_vid($id_or_url){
	return WPJAM_Utli::get_qqv_id($id_or_url);
}


function wpjam_is_mobile() {
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wp_is_mobile');
	return wp_is_mobile();
}

function get_post_first_image($post_content=''){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_first_image');
	return wpjam_get_post_first_image($post_content);
}

function wpjam_get_post_image_url($image_id, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wp_get_attachment_image_url');
	
	if($thumb = wp_get_attachment_image_src($image_id, $size)){
		return $thumb[0];
	}
	return false;	
}

function wpjam_get_post_thumbnail_src($post=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_thumbnail_url');
	return wpjam_get_post_thumbnail_url($post, $size, $crop, $retina);
}

function wpjam_get_post_thumbnail_uri($post=null, $size='full'){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_post_thumbnail_url');
	return wpjam_get_post_thumbnail_url($post, $size);
}

function wpjam_get_default_thumbnail_src($size){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_default_thumbnail_url');
	return wpjam_get_default_thumbnail_url($size);
}

function wpjam_get_default_thumbnail_uri(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_default_thumbnail_url');
	return wpjam_get_default_thumbnail_url('full');
}

/* category thumbnail */
function wpjam_has_category_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_category_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);
}

function wpjam_get_category_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-category-image", $retina=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $retina);
}

function wpjam_category_thumbnail($size='thumbnail', $crop=1, $class="wp-category-image", $retina=2){
	wpjam_term_thumbnail($size, $crop, $class, $retina);
}

function wpjam_get_category_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);	
}

function wpjam_get_category_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

/* tag thumbnail */
function wpjam_has_tag_thumbnail(){
	return wpjam_has_term_thumbnail();
}

function wpjam_get_tag_thumbnail_url($term=null, $size='full', $crop=1, $retina=1){
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);
}

function wpjam_get_tag_thumbnail($term=null, $size='thumbnail', $crop=1, $class="wp-tag-image", $retina=2){
	return wpjam_get_term_thumbnail($term, $size, $crop, $class, $retina);
}

function wpjam_tag_thumbnail($size='thumbnail', $crop=1, $class="wp-tag-image", $retina=2){
	wpjam_term_thumbnail($size, $crop, $class, $retina);
}

function wpjam_get_tag_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);	
}

function wpjam_get_tag_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

function wpjam_get_term_thumbnail_src($term=null, $size='thumbnail', $crop=1, $retina=1){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, $size, $crop, $retina);	
}

function wpjam_get_term_thumbnail_uri($term=null){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 3.2', 'wpjam_get_term_thumbnail_url');
	return wpjam_get_term_thumbnail_url($term, 'full');
}

function wpjam_display_errors(){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2');
}

// 逐步放弃
function wpjam_get_form_fields($admin_column = false){
	_deprecated_function(__FUNCTION__, 'WPJAM Basic 4.2');
	return [];
}


class WPJAM_PlatformBit extends WPJAM_Bit{
	public function __construct($bit){
		$this->set_platform($bit);
	}

	public function set_platform($bit){
		$this->bit	= $bit;
	}

	public function get_platform(){
		return $this->bit;
	}
}
