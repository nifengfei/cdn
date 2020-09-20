<?php
add_filter('disable_months_dropdown', '__return_true');

add_filter('views_edit-template',	function($views){
	unset($views['publish']);

	$current	= $_GET['template_type'] ?? null;
	$template_types	= wpjam_get_content_template_types();

	foreach ($template_types as $type=>$tt) {
		$query_args	= ['no_found_rows'=>false, 'post_type'=>'template',	'meta_key'=>'_template_type'];

		if($type == 'content'){
			$query_args['meta_compare']	= 'NOT EXISTS';
		}else{
			$query_args['meta_value']	= $type;	
		}

		$query	= wpjam_query($query_args);

		if($count	= $query->found_posts){
			$class	= ($current && $current == $type) ?' class="current"' : '';
			$views[$type.'-content']	='<a href="edit.php?post_type=template&template_type='.$type.'"'.$class.'><span class="dashicons dashicons-'.$tt['dashicon'].'"></span> '.$tt['title'].'<span class="count">（'.$count.'）</span></a>';
		}
	}

	return $views;
},1,2);

add_action('pre_get_posts', function($query){
	if($query->is_main_query()){
		$template_type	= $_GET['template_type'] ?? null;

		if($template_type){
			$query->set('meta_key', '_template_type');

			if($template_type == 'content'){
				$query->set('meta_compare', 'NOT EXISTS');
			}else{
				$query->set('meta_value', $template_type);
			}
		}
	}
});

add_filter('manage_template_posts_columns',function($columns){
	wpjam_array_push($columns, ['shortcode'=>'短代码','template_type'=>'模板类型'], 'date'); 
	return $columns;
});

add_action('manage_template_posts_custom_column', function($column_name, $post_id){
	if($column_name == 'shortcode'){
		echo '[template id="'.$post_id.'"]';
	}elseif($column_name == 'template_type'){
		$template_types	= wpjam_get_content_template_types();
		$template_type	= get_post_meta($post_id, '_template_type', true) ?: 'content';
		if($template_type && isset($template_types[$template_type])){
			$tt = $template_types[$template_type];
			echo '<span class="dashicons dashicons-'.$tt['dashicon'].'"></span>  '.$tt['title'];
		}else{
			echo '内容模板';
		}
	}
}, 10, 2);

add_action('admin_head', function(){
	?>
	<script type="text/javascript">
	jQuery(function($){
		$('body').on('click', '.page-title-action', function(){
			tb_show('选择模板类型', '#TB_inline?inlineId=select_template_type&width=400&height=200');
			tb_position();
		});

		$('body').on('submit', "#template_type_form", function(e){
			e.preventDefault();	// 阻止事件默认行为。
			var url = $("input[name='template_type']:checked").parent().data('url');
			window.location.replace(url);
		});
	});
	</script>

	<style type="text/css">
	ul.subsubsub .dashicons{
		vertical-align: text-bottom;
	}
	</style>

	<?php
});

add_action('admin_footer', function(){
	echo '<div id="select_template_type">';

	$template_types	= wpjam_get_content_template_types();

	foreach ($template_types as $type=>&$tt) {
		$tt['title']	= '<span class="dashicons dashicons-'.$tt['dashicon'].'"></span> '.$tt['title'];
		if($type == 'content'){
			$tt['url']	= admin_url('post-new.php?post_type=template');	
		}else{
			$tt['url']	= admin_url('edit.php?post_type=template&page=wpjam-'.$type);
		}
	}
	
	$fields	=[
		'template_type'	=> ['title'=>'',	'type'=>'radio',	'options'=>$template_types,	'sep'=>'<br /><br />']
	];

	wpjam_ajax_form([
		'fields'		=> $fields,
		'action'		=> 'select_template_type',
		'form_id'		=> 'template_type_form',
		'submit_text'	=> '新建'
	]);

	echo '</div>';
});

add_filter('wpjam_html_replace', function($html){
	return preg_replace('/<a href=".*?" class="page-title-action">.*?<\/a>/i', '<a href="javascript:;" class="page-title-action">新增模板</a>', $html);
});