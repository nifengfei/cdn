<?php
add_action('admin_head',function(){ ?>

	<style type="text/css">
		#tr_cat_banner_img,#tr_cat_banner_text_align,#div_cat_banner_img,#div_cat_banner_text_align{display: none}
		#div_cat_banner_type #label_cat_banner_type_1,#div_cat_banner_type #label_cat_banner_type_2,#div_cat_banner_text_align #label_cat_banner_text_align_left,#div_cat_banner_text_align #label_cat_banner_text_align_center,#label_cat_list_type_col_3,#label_cat_list_type_col_3_sidebar,#label_cat_list_type_col_4,#label_cat_list_type_list{margin-right: 10px;display: inline-block}
		.form-field > label {font-size: 14px;font-weight: 600 !important;margin-bottom: 10px}
	</style>

	<script type="text/javascript">
	jQuery(function($){
		
		$('tr#tr_cat_banner_img').hide();
		$('tr#tr_cat_banner_text_align').hide();
		$('#div_cat_banner_img').hide();
		$('#div_cat_banner_text_align').hide();
		
		$('body').on('change', '#cat_banner_type_options input', function(){
			$('tr#tr_cat_banner_img').show();
			$('tr#tr_cat_banner_text_align').show();
			$('#div_cat_banner_img').show();
			$('#div_cat_banner_text_align').show();
			if ($(this).is(':checked')) {
				if($(this).val() != '2'){
					$('tr#tr_cat_banner_img').hide();
					$('tr#tr_cat_banner_text_align').hide();
					$('#div_cat_banner_img').hide();
					$('#div_cat_banner_text_align').hide();
				}
			}			
		});

		//【优化选中显示】当选中为Banner样式2的时候，即使刷新页面，也会默认显示上传背景图像和选中显示位置
		if(document.getElementById("cat_banner_type_2").checked){
			$('tr#tr_cat_banner_img').show();
			$('tr#tr_cat_banner_text_align').show();
		}else{
			$('tr#tr_cat_banner_img').hide();
			$('tr#tr_cat_banner_text_align').hide();
		} 

		//$('select#cat_banner_type').change();
	});
	</script>

<?php });


add_filter('wpjam_category_term_options',function ($post_options){
	$term_options['cat_list_type']	= ['title'=>'列表样式', 'type'=>'radio', 'options'=>['col_3'=>'网格*3','col_3_sidebar'=>'网格*3+侧栏','col_4'=>'网格*4（宽版则显示5列）','list'=>'博客列表+侧栏','noimg_list'=>'无图列表+侧栏']];
	$term_options['cat_banner_type'] = ['title'=>'Banner 样式', 'type'=>'radio', 'options'=>['1'=>'常规样式','2'=>'背景图像+分类标题+分类描述']];
	$term_options['cat_banner_img']	= ['title'=>'Banner 背景图像', 'type'=>'img', 'item_type'=>'url', 'size'=>'152*50', 'description'=>'建议尺寸：1920*462'];
	$term_options['cat_banner_text_align']	= ['title'=>'分类标题+描述 显示位置',	'type'=>'radio', 'options'=>['left'=>'居左','center'=>'居中']];


	
	return $term_options;
});