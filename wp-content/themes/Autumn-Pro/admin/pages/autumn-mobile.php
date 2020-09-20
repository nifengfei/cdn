<?php
if(!WPJAM_Verify::verify()){
	wp_redirect(admin_url('admin.php?page=wpjam-basic'));
	exit;		
}

add_action('admin_head',function(){ ?>

<style>
	#tr_mobile_foot_menu .wpjam-img.default{width:105px;height:70px}
	#tr_mobile_foot_menu .sub-field-label{font-weight:400}
	.form-table #tr_mobile_foot_menu input[type="radio"]{margin-top: -2px}
	#tr_mobile_foot_menu .sub-field-detail span{margin-right:10px}

	#sub_field_mobile_foot_menu_img_0,#sub_field_mobile_foot_menu_img_1,#sub_field_mobile_foot_menu_img_2,#sub_field_mobile_foot_menu_img_3,#sub_field_mobile_foot_menu_img_4,#sub_field_mobile_foot_menu_img_5,#sub_field_mobile_foot_menu_img_6,#sub_field_mobile_foot_menu_img_7,#sub_field_mobile_foot_menu_img_8,#sub_field_mobile_foot_menu_img_9,#sub_field_mobile_foot_menu_img_10,#sub_field_mobile_foot_menu_img_text_0,#sub_field_mobile_foot_menu_img_text_1,#sub_field_mobile_foot_menu_img_text_2,#sub_field_mobile_foot_menu_img_text_3,#sub_field_mobile_foot_menu_img_text_4,#sub_field_mobile_foot_menu_img_text_5,#sub_field_mobile_foot_menu_img_text_6,#sub_field_mobile_foot_menu_img_text_7,#sub_field_mobile_foot_menu_img_text_8,#sub_field_mobile_foot_menu_img_text_9,#sub_field_mobile_foot_menu_img_text_10{display:none}

	/*#tr_mobile_foot_menu .mu-item:nth-child(n+6){display:none} 超出5个就隐藏*/

</style>

<script type="text/javascript">
jQuery(function($){

	//$("#tr_mobile_foot_menu .mu-item:nth-child(n+6)").remove(); //超出5个就删除
	
	//菜单类型

	$('body').on('change', '#sub_field_mobile_foot_menu_type_0 input', function(){
		$('#sub_field_mobile_foot_menu_img_0').hide();
		$('#sub_field_mobile_foot_menu_img_text_0').hide();

		if ($(this).is(':checked')) {

			if($(this).val() == 'img'){
				$('#sub_field_mobile_foot_menu_img_0').show();
				$('#sub_field_mobile_foot_menu_url_0').hide();
				$('#sub_field_mobile_foot_menu_text_0').show();
				$('#sub_field_mobile_foot_menu_icon_0').show();
				$('#sub_field_mobile_foot_menu_img_text_0').show();
			}
			if($(this).val() == 'user'){
				$('#sub_field_mobile_foot_menu_img_0').hide();
				$('#sub_field_mobile_foot_menu_url_0').hide();
				$('#sub_field_mobile_foot_menu_text_0').hide();
				$('#sub_field_mobile_foot_menu_icon_0').hide();
				$('#sub_field_mobile_foot_menu_img_text_0').hide();
			}
			if($(this).val() == 'link'){
				$('#sub_field_mobile_foot_menu_img_0').hide();
				$('#sub_field_mobile_foot_menu_url_0').show();
				$('#sub_field_mobile_foot_menu_text_0').show();
				$('#sub_field_mobile_foot_menu_icon_0').show();
				$('#sub_field_mobile_foot_menu_img_text_0').hide();
			}

		}

	});
	if(document.getElementById("mobile_foot_menu_type_0_link").checked){
		$('#sub_field_mobile_foot_menu_img_0').hide();
		$('#sub_field_mobile_foot_menu_url_0').show();
		$('#sub_field_mobile_foot_menu_text_0').show();
		$('#sub_field_mobile_foot_menu_icon_0').show();
		$('#sub_field_mobile_foot_menu_img_text_0').hide();
	}else if(document.getElementById("mobile_foot_menu_type_0_user").checked){
		$('#sub_field_mobile_foot_menu_img_0').hide();
		$('#sub_field_mobile_foot_menu_url_0').hide();
		$('#sub_field_mobile_foot_menu_text_0').hide();
		$('#sub_field_mobile_foot_menu_icon_0').hide();
		$('#sub_field_mobile_foot_menu_img_text_0').hide();
	}else if(document.getElementById("mobile_foot_menu_type_0_home").checked){
		$('#sub_field_mobile_foot_menu_img_0').hide();
		$('#sub_field_mobile_foot_menu_url_0').hide();
		$('#sub_field_mobile_foot_menu_text_0').hide();
		$('#sub_field_mobile_foot_menu_icon_0').hide();
		$('#sub_field_mobile_foot_menu_img_text_0').hide();
	}else{
		$('#sub_field_mobile_foot_menu_img_0').show();
		$('#sub_field_mobile_foot_menu_url_0').hide();
		$('#sub_field_mobile_foot_menu_img_text_0').show();
	}


	$('body').on('change', '#sub_field_mobile_foot_menu_type_1 input', function(){
		$('#sub_field_mobile_foot_menu_img_1').hide();
		$('#sub_field_mobile_foot_menu_img_text_1').hide();

		if ($(this).is(':checked')) {

			if($(this).val() == 'img'){
				$('#sub_field_mobile_foot_menu_img_1').show();
				$('#sub_field_mobile_foot_menu_url_1').hide();
				$('#sub_field_mobile_foot_menu_text_1').show();
				$('#sub_field_mobile_foot_menu_icon_1').show();
				$('#sub_field_mobile_foot_menu_img_text_1').show();
			}
			if($(this).val() == 'user'){
				$('#sub_field_mobile_foot_menu_img_1').hide();
				$('#sub_field_mobile_foot_menu_url_1').hide();
				$('#sub_field_mobile_foot_menu_text_1').hide();
				$('#sub_field_mobile_foot_menu_icon_1').hide();
				$('#sub_field_mobile_foot_menu_img_text_1').hide();
			}
			if($(this).val() == 'home'){
				$('#sub_field_mobile_foot_menu_img_1').hide();
				$('#sub_field_mobile_foot_menu_url_1').hide();
				$('#sub_field_mobile_foot_menu_text_1').hide();
				$('#sub_field_mobile_foot_menu_icon_1').hide();
				$('#sub_field_mobile_foot_menu_img_text_1').hide();
			}
			if($(this).val() == 'link'){
				$('#sub_field_mobile_foot_menu_img_1').hide();
				$('#sub_field_mobile_foot_menu_url_1').show();
				$('#sub_field_mobile_foot_menu_text_1').show();
				$('#sub_field_mobile_foot_menu_icon_1').show();
				$('#sub_field_mobile_foot_menu_img_text_1').hide();
			}

		}

	});
	if(document.getElementById("mobile_foot_menu_type_1_link").checked){
		$('#sub_field_mobile_foot_menu_img_1').hide();
		$('#sub_field_mobile_foot_menu_url_1').show();
		$('#sub_field_mobile_foot_menu_text_1').show();
		$('#sub_field_mobile_foot_menu_icon_1').show();
		$('#sub_field_mobile_foot_menu_img_text_1').hide();
	}else if(document.getElementById("mobile_foot_menu_type_1_user").checked){
		$('#sub_field_mobile_foot_menu_img_1').hide();
		$('#sub_field_mobile_foot_menu_url_1').hide();
		$('#sub_field_mobile_foot_menu_text_1').hide();
		$('#sub_field_mobile_foot_menu_icon_1').hide();
		$('#sub_field_mobile_foot_menu_img_text_1').hide();
	}else if(document.getElementById("mobile_foot_menu_type_1_home").checked){
		$('#sub_field_mobile_foot_menu_img_1').hide();
		$('#sub_field_mobile_foot_menu_url_1').hide();
		$('#sub_field_mobile_foot_menu_text_1').hide();
		$('#sub_field_mobile_foot_menu_icon_1').hide();
		$('#sub_field_mobile_foot_menu_img_text_1').hide();
	}else{
		$('#sub_field_mobile_foot_menu_img_1').show();
		$('#sub_field_mobile_foot_menu_url_1').hide();
		$('#sub_field_mobile_foot_menu_img_text_1').show();
	}


	$('body').on('change', '#sub_field_mobile_foot_menu_type_2 input', function(){
		$('#sub_field_mobile_foot_menu_img_2').hide();
		$('#sub_field_mobile_foot_menu_img_text_2').hide();

		if ($(this).is(':checked')) {

			if($(this).val() == 'img'){
				$('#sub_field_mobile_foot_menu_img_2').show();
				$('#sub_field_mobile_foot_menu_url_2').hide();
				$('#sub_field_mobile_foot_menu_text_2').show();
				$('#sub_field_mobile_foot_menu_icon_2').show();
				$('#sub_field_mobile_foot_menu_img_text_2').show();
			}
			if($(this).val() == 'user'){
				$('#sub_field_mobile_foot_menu_img_2').hide();
				$('#sub_field_mobile_foot_menu_url_2').hide();
				$('#sub_field_mobile_foot_menu_text_2').hide();
				$('#sub_field_mobile_foot_menu_icon_2').hide();
				$('#sub_field_mobile_foot_menu_img_text_2').hide();
			}
			if($(this).val() == 'home'){
				$('#sub_field_mobile_foot_menu_img_2').hide();
				$('#sub_field_mobile_foot_menu_url_2').hide();
				$('#sub_field_mobile_foot_menu_text_2').hide();
				$('#sub_field_mobile_foot_menu_icon_2').hide();
				$('#sub_field_mobile_foot_menu_img_text_2').hide();
			}
			if($(this).val() == 'link'){
				$('#sub_field_mobile_foot_menu_img_2').hide();
				$('#sub_field_mobile_foot_menu_url_2').show();
				$('#sub_field_mobile_foot_menu_text_2').show();
				$('#sub_field_mobile_foot_menu_icon_2').show();
				$('#sub_field_mobile_foot_menu_img_text_2').hide();
			}

		}

	});
	if(document.getElementById("mobile_foot_menu_type_2_link").checked){
		$('#sub_field_mobile_foot_menu_img_2').hide();
		$('#sub_field_mobile_foot_menu_url_2').show();
		$('#sub_field_mobile_foot_menu_text_2').show();
		$('#sub_field_mobile_foot_menu_icon_2').show();
		$('#sub_field_mobile_foot_menu_img_text_2').hide();
	}else if(document.getElementById("mobile_foot_menu_type_2_user").checked){
		$('#sub_field_mobile_foot_menu_img_2').hide();
		$('#sub_field_mobile_foot_menu_url_2').hide();
		$('#sub_field_mobile_foot_menu_text_2').hide();
		$('#sub_field_mobile_foot_menu_icon_2').hide();
		$('#sub_field_mobile_foot_menu_img_text_2').hide();
	}else if(document.getElementById("mobile_foot_menu_type_2_home").checked){
		$('#sub_field_mobile_foot_menu_img_2').hide();
		$('#sub_field_mobile_foot_menu_url_2').hide();
		$('#sub_field_mobile_foot_menu_text_2').hide();
		$('#sub_field_mobile_foot_menu_icon_2').hide();
		$('#sub_field_mobile_foot_menu_img_text_2').hide();
	}else{
		$('#sub_field_mobile_foot_menu_img_2').show();
		$('#sub_field_mobile_foot_menu_url_2').hide();
		$('#sub_field_mobile_foot_menu_img_text_2').show();
	}


	$('body').on('change', '#sub_field_mobile_foot_menu_type_3 input', function(){
		$('#sub_field_mobile_foot_menu_img_3').hide();
		$('#sub_field_mobile_foot_menu_img_text_3').hide();

		if ($(this).is(':checked')) {

			if($(this).val() == 'img'){
				$('#sub_field_mobile_foot_menu_img_3').show();
				$('#sub_field_mobile_foot_menu_url_3').hide();
				$('#sub_field_mobile_foot_menu_text_3').show();
				$('#sub_field_mobile_foot_menu_icon_3').show();
				$('#sub_field_mobile_foot_menu_img_text_3').show();
			}
			if($(this).val() == 'user'){
				$('#sub_field_mobile_foot_menu_img_3').hide();
				$('#sub_field_mobile_foot_menu_url_3').hide();
				$('#sub_field_mobile_foot_menu_text_3').hide();
				$('#sub_field_mobile_foot_menu_icon_3').hide();
				$('#sub_field_mobile_foot_menu_img_text_3').hide();
			}
			if($(this).val() == 'home'){
				$('#sub_field_mobile_foot_menu_img_3').hide();
				$('#sub_field_mobile_foot_menu_url_3').hide();
				$('#sub_field_mobile_foot_menu_text_3').hide();
				$('#sub_field_mobile_foot_menu_icon_3').hide();
				$('#sub_field_mobile_foot_menu_img_text_3').hide();
			}
			if($(this).val() == 'link'){
				$('#sub_field_mobile_foot_menu_img_3').hide();
				$('#sub_field_mobile_foot_menu_url_3').show();
				$('#sub_field_mobile_foot_menu_text_3').show();
				$('#sub_field_mobile_foot_menu_icon_3').show();
				$('#sub_field_mobile_foot_menu_img_text_3').hide();
			}

		}

	});
	if(document.getElementById("mobile_foot_menu_type_3_link").checked){
		$('#sub_field_mobile_foot_menu_img_3').hide();
		$('#sub_field_mobile_foot_menu_url_3').show();
		$('#sub_field_mobile_foot_menu_text_3').show();
		$('#sub_field_mobile_foot_menu_icon_3').show();
		$('#sub_field_mobile_foot_menu_img_text_3').hide();
	}else if(document.getElementById("mobile_foot_menu_type_3_user").checked){
		$('#sub_field_mobile_foot_menu_img_3').hide();
		$('#sub_field_mobile_foot_menu_url_3').hide();
		$('#sub_field_mobile_foot_menu_text_3').hide();
		$('#sub_field_mobile_foot_menu_icon_3').hide();
		$('#sub_field_mobile_foot_menu_img_text_3').hide();
	}else if(document.getElementById("mobile_foot_menu_type_3_home").checked){
		$('#sub_field_mobile_foot_menu_img_3').hide();
		$('#sub_field_mobile_foot_menu_url_3').hide();
		$('#sub_field_mobile_foot_menu_text_3').hide();
		$('#sub_field_mobile_foot_menu_icon_3').hide();
		$('#sub_field_mobile_foot_menu_img_text_3').hide();
	}else{
		$('#sub_field_mobile_foot_menu_img_3').show();
		$('#sub_field_mobile_foot_menu_url_3').hide();
		$('#sub_field_mobile_foot_menu_img_text_3').show();
	}


	$('body').on('change', '#sub_field_mobile_foot_menu_type_4 input', function(){
		$('#sub_field_mobile_foot_menu_img_4').hide();
		$('#sub_field_mobile_foot_menu_img_text_4').hide();

		if ($(this).is(':checked')) {

			if($(this).val() == 'img'){
				$('#sub_field_mobile_foot_menu_img_4').show();
				$('#sub_field_mobile_foot_menu_url_4').hide();
				$('#sub_field_mobile_foot_menu_text_4').show();
				$('#sub_field_mobile_foot_menu_icon_4').show();
				$('#sub_field_mobile_foot_menu_img_text_4').show();
			}
			if($(this).val() == 'user'){
				$('#sub_field_mobile_foot_menu_img_4').hide();
				$('#sub_field_mobile_foot_menu_url_4').hide();
				$('#sub_field_mobile_foot_menu_text_4').hide();
				$('#sub_field_mobile_foot_menu_icon_4').hide();
				$('#sub_field_mobile_foot_menu_img_text_4').hide();
			}
			if($(this).val() == 'home'){
				$('#sub_field_mobile_foot_menu_img_4').hide();
				$('#sub_field_mobile_foot_menu_url_4').hide();
				$('#sub_field_mobile_foot_menu_text_4').hide();
				$('#sub_field_mobile_foot_menu_icon_4').hide();
				$('#sub_field_mobile_foot_menu_img_text_4').hide();
			}
			if($(this).val() == 'link'){
				$('#sub_field_mobile_foot_menu_img_4').hide();
				$('#sub_field_mobile_foot_menu_url_4').show();
				$('#sub_field_mobile_foot_menu_text_4').show();
				$('#sub_field_mobile_foot_menu_icon_4').show();
				$('#sub_field_mobile_foot_menu_img_text_4').hide();
			}

		}

	});
	if(document.getElementById("mobile_foot_menu_type_4_link").checked){
		$('#sub_field_mobile_foot_menu_img_4').hide();
		$('#sub_field_mobile_foot_menu_url_4').show();
		$('#sub_field_mobile_foot_menu_text_4').show();
		$('#sub_field_mobile_foot_menu_icon_4').show();
		$('#sub_field_mobile_foot_menu_img_text_4').hide();
	}else if(document.getElementById("mobile_foot_menu_type_4_user").checked){
		$('#sub_field_mobile_foot_menu_img_4').hide();
		$('#sub_field_mobile_foot_menu_url_4').hide();
		$('#sub_field_mobile_foot_menu_text_4').hide();
		$('#sub_field_mobile_foot_menu_icon_4').hide();
		$('#sub_field_mobile_foot_menu_img_text_4').hide();
	}else if(document.getElementById("mobile_foot_menu_type_4_home").checked){
		$('#sub_field_mobile_foot_menu_img_4').hide();
		$('#sub_field_mobile_foot_menu_url_4').hide();
		$('#sub_field_mobile_foot_menu_text_4').hide();
		$('#sub_field_mobile_foot_menu_icon_4').hide();
		$('#sub_field_mobile_foot_menu_img_text_4').hide();
	}else{
		$('#sub_field_mobile_foot_menu_img_4').show();
		$('#sub_field_mobile_foot_menu_url_4').hide();
		$('#sub_field_mobile_foot_menu_img_text_4').show();
	}



	$('body').on('change', '#div_mobile_foot_menu_no input', function(){
		$('#tr_mobile_foot_menu').hide();
		$('#div_mobile_foot_menu_700').hide();

		if ($(this).is(':checked')) {
			$('#tr_mobile_foot_menu').show();
			$('#div_mobile_foot_menu_700').show();
		}

	});
	if(document.getElementById("mobile_foot_menu_no").checked){
		$('#tr_mobile_foot_menu').show();
		$('#div_mobile_foot_menu_700').show();
	}else{
		$('#tr_mobile_foot_menu').hide();
		$('#div_mobile_foot_menu_700').hide();
	}


});
</script>

<?php });

add_filter('wpjam_theme_setting', function(){
	
	$mobile_sub_fields		= [
		'mobile_no_sidebar'		=> '手机端隐藏侧边栏内容，除「菜单栏」外，将不显示侧栏内容',
		'mobile_foot_menu_no'	=> '开启手机端底部菜单【最多只能添加5个菜单】',
		'mobile_foot_menu_700'	=> '手机端底部菜单，字体加粗！',
	];
	$mobile_sub_fields		= array_map(function($desc){return ['title'=>'','type'=>'checkbox','description'=>$desc]; }, $mobile_sub_fields);

	$fields	= [

		'mobile_setting'	=>['title'=>'扩展选项',	'type'=>'fieldset',	'fields'=>$mobile_sub_fields],

		'mobile_foot_menu'	=> ['title'=>'手机端底部菜单', 'type'=>'mu-fields',	'total'=>5, 'fields'=>[

			'mobile_foot_menu_type'		=> ['title'=>'菜单类型', 'type'=>'radio', 'options'=>['link'=>'跳转链接','img'=>'弹出二维码','user'=>'登录/用户中心','home'=>'首页(必须放在第一位)']],
			'mobile_foot_menu_img'		=> ['title'=>'上传二维码', 'type'=>'img', 'item_type'=>'url', 'description'=>'建议尺寸：200*200 px'],
			'mobile_foot_menu_img_text'	=> ['title'=>'二维码标题', 'type'=>'text', 'class'=>'all-options'],
			'mobile_foot_menu_text'		=> ['title'=>'菜单名字',	'type'=>'text', 'class'=>'all-options'],
			'mobile_foot_menu_icon'		=> ['title'=>'菜单图标',	'type'=>'text',	'class'=>'all-options'],
			'mobile_foot_menu_url'		=> ['title'=>'跳转链接',	'type'=>'text',	'class'=>'all-options'],

		]],

	];

	$ajax = false;

	return compact('fields','ajax');
});