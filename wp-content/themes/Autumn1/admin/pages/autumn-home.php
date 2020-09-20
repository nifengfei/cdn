<?php
add_action('admin_head',function(){ ?>
<style type="text/css">
	#tr_slide_type_img .wpjam-img.default{width: 75px;height: 50px}
	#tr_slide_type_img label.sub-field-label{font-weight: 400}
	#tr_slide_type_img input.all-options{width: 500px}

	#slide_region_options label{
	display:inline-block;
	width:156px;
	height:111px;
	background-repeat:no-repeat;
	background-size: contain;
	margin-right:10px;
	}

	#slide_region_options input{
		display: none;
	}
	
	<?php for ($i=1; $i<=5; $i++) { ?>

	#label_slide_region_<?php echo $i; ?>{	
	background-image: url(<?php echo get_stylesheet_directory_uri().'/static/images/set/slide-'.$i.'.png';?>);
	}

	#label_slide_region_<?php echo $i; ?> #slide_region_<?php echo $i; ?>:checked {
		border:2px solid #1e8cbe;
		width: 100%;
		height: 0;
		border-radius: 0;
		background-image: url(<?php echo get_stylesheet_directory_uri().'/static/images/set/slide-'.$i.'.png';?>);
		display: block;
	}

	<?php } ?>
	
</style>

<script type="text/javascript">
jQuery(function($){
	
	$('tr#tr_slide_region').show();
	$('tr#tr_slide_post_id').show();
	$('tr#tr_slide_type_img').hide();
	$('tr#tr_img_one_url').hide();
	$('tr#tr_img_one_url_mb').hide();
	$('tr#tr_img_one_title').hide();
	$('tr#tr_img_one_ms').hide();
	
	$('body').on('change', '#slide_type_options input', function(){
		$('tr#tr_slide_region').show();
		$('tr#tr_slide_post_id').show();
		$('tr#tr_slide_type_img').hide();
		$('tr#tr_img_one_url').hide();
		$('tr#tr_img_one_url_mb').hide();
		$('tr#tr_img_one_title').hide();
		$('tr#tr_img_one_ms').hide();
		if ($(this).is(':checked')) {
			if($(this).val() != 'post'){
				$('tr#tr_slide_region').hide();
				$('tr#tr_slide_post_id').hide();
				$('tr#tr_slide_type_img').show();
				$('tr#tr_slide_bg_img').show();
				$('tr#tr_img_one_url').hide();
				$('tr#tr_img_one_url_mb').hide();
				$('tr#tr_img_one_title').hide();
				$('tr#tr_img_one_ms').hide();
			}
			if($(this).val() == 'img_one'){
				$('tr#tr_slide_region').hide();
				$('tr#tr_slide_post_id').hide();
				$('tr#tr_slide_type_img').hide();
				$('tr#tr_slide_bg_img').hide();
				$('tr#tr_img_one_url').show();
				$('tr#tr_img_one_url_mb').show();
				$('tr#tr_img_one_title').show();
				$('tr#tr_img_one_ms').show();
			}
		}			
	});

	//【优化选中显示】当选中幻灯片为图像类型的时候，即使刷新页面，也会默认显示添加幻灯片的选项
	if(document.getElementById("slide_type_post").checked){
		$('tr#tr_slide_region').show();
		$('tr#tr_slide_post_id').show();
		$('tr#tr_slide_type_img').hide();
	}else if(document.getElementById("slide_type_img_one").checked){
		$('tr#tr_slide_region').hide();
		$('tr#tr_slide_post_id').hide();
		$('tr#tr_slide_type_img').hide();
		$('tr#tr_slide_bg_img').hide();
		$('tr#tr_img_one_url').show();
		$('tr#tr_img_one_url_mb').show();
		$('tr#tr_img_one_title').show();
		$('tr#tr_img_one_ms').show();
	}else{
		$('tr#tr_slide_region').hide();
		$('tr#tr_slide_post_id').hide();
		$('tr#tr_slide_type_img').show();
	}


	//
	$('tr#tr_slide_bg_img').hide();
	$('body').on('change', '#slide_region_options input', function(){
		$('tr#tr_slide_bg_img').show();
		if ($(this).is(':checked')) {
			if($(this).val() != '4'){
				$('tr#tr_slide_bg_img').hide();
			}
		}			
	});

	if(document.getElementById("slide_region_4").checked){
		$('tr#tr_slide_bg_img').show();
	}else{
		$('tr#tr_slide_bg_img').hide();
	}
});
</script>

<?php });

add_filter('wpjam_theme_setting', function(){
	$sections	= [
		

		'banner'	=>[
			'title'		=>'首页 Banner', 
			'fields'	=>[
				'slide_type'		=> ['title'=>'Banner 类型', 'type'=>'radio', 'options'=>['post'=>'指定文章','img'=>'图片轮播','img_one'=>'单张图片']],

				'slide_type_img'	=> ['title'=>'图片轮播选项', 'type'=>'mu-fields', 'fields'=>[

					'img_url'		=> ['title'=>'上传图像（PC端）', 'type'=>'img', 'item_type'=>'url', 'description'=>'没有建议尺寸，喜欢就好'],
					'img_url_mb'	=> ['title'=>'上传图像（手机端）', 'type'=>'img', 'item_type'=>'url', 'description'=>'建议尺寸：750*580'],
					'img_title'		=> ['title'=>'图片标题',	'type'=>'text', 'class'=>'all-options'],
					'img_ms'		=> ['title'=>'图片描述',	'type'=>'text',	'class'=>'all-options'],
					
					'img_btn1_txt'	=> ['title'=>'按钮-1 标题',	'type'=>'text',	'class'=>'all-options'],
					'img_btn1_url'	=> ['title'=>'按钮-1 链接',	'type'=>'text',	'class'=>'all-options'],
					
					'img_btn2_txt'	=> ['title'=>'按钮-2 标题',	'type'=>'text',	'class'=>'all-options'],
					'img_btn2_url'	=> ['title'=>'按钮-2 链接',	'type'=>'text',	'class'=>'all-options'],

				]],

				'img_one_url'		=> ['title'=>'上传Banner图像（PC端）', 'type'=>'img', 'item_type'=>'url', 'description'=>'没有建议尺寸，越大越好，喜欢就好'],
				'img_one_url_mb'	=> ['title'=>'上传Banner图像（手机端）', 'type'=>'img', 'item_type'=>'url', 'description'=>'建议尺寸：750*580'],
				'img_one_title'		=> ['title'=>'Banner标题', 'type'=>'text', 'rows'=>4],
				'img_one_ms'		=> ['title'=>'Banner描述', 'type'=>'text', 'rows'=>4],

				'slide_region'		=> ['title'=>'文章轮播样式',	'type'=>'radio',	'options'=>['1'=>'','2'=>'','4'=>'','3'=>'','5'=>''], 'show_admin_column'=>true],
				'slide_bg_img'		=>['title'=>'背景图像',				'type'=>'img',		'item_type'=>'url',	'description'=>'上传一张背景图像'],
				'slide_post_id'		=>['title'=>'调用文章', 'type'=>'mu-text', 'data_type'=>'post_type', 'post_type'=>'post', 'class'=>'all-options', 'placeholder'=>'', 'description'=>'请输入文章ID或者关键字进行筛选'],
			]
		],

		'index_cat'	=> [
			'title'		=> '分类模块',
			'fields'	=> [
				'index_cat'			=> ['title'=>'分类模块', 'type'=>'radio', 'options'=>['1'=>'关闭','2'=>'一组3个，超出可轮播','3'=>'一组4个，超出可轮播']],
				'index_cat_lb'		=> ['title'=>'轮播按钮',	'type'=>'checkbox',	'description'=>'显示轮播切换按钮，分类过多时候可以轮播切换'],
				'index_cat_id'		=> ['title'=>'填写分类id', 'type'=>'mu-text', 'description'=>'可添加多个id，拖动排序【此项仅在分类模块（上一条选项）开启后生效】'],
			]
		],

		'index_list'	=> [
			'title'		=> '文章列表',
			'fields'	=> [
				'list_region'		=> ['title'=>'文章列表',			'type'=>'radio',	'options'=>['col_3'=>'网格*3','col_3_sidebar'=>'网格*3+侧栏','col_4'=>'网格*4（宽版则显示5列）','list'=>'博客列表+侧栏','noimg_list'=>'无图列表+侧栏']],
				'new_title'			=> ['title'=>'模块标题',	'type'=>'checkbox',	'description'=>'显示【最新文章】标题'],
			]
		]

		
	];

	return compact('sections');
});