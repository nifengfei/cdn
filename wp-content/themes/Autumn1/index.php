<?php get_header(); ?>
<?php if(is_home()){ get_template_part('template-parts/banner'); }?>

<?php
$cat_banner_type = get_term_meta($cat, 'cat_banner_type', true);
$cat_banner_img = get_term_meta($cat, 'cat_banner_img', true);
$text_align = get_term_meta($cat, 'cat_banner_text_align', true);
$thiscat = get_category($cat);
$category_description = category_description();
if( $cat_banner_type == '2' ){?>
<div class="page-banner" style="background: url(<?php echo $cat_banner_img; ?>);background-position: center center;-webkit-background-size: cover;background-size: cover;">
	<div class="dark-overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="page-content"<?php if( $text_align == 'left' ){?> style="text-align: left;"<?php }?>>
					<h2><?php echo $thiscat ->name;?></h2>
					<p class="text-muted lead">
					<?php
					if($category_description){
						echo $category_description;
					}else{
						echo '请在【后台 – 文章 – 分类 – 编辑 – 图像描述】中输入文本描述…';
					}?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php }?>

<div class="site-content container">
	<div class="row">
		<?php
			if(is_category()){
				$list_region = get_term_meta($cat, 'cat_list_type', true);
			}else{
				$list_region	= wpjam_theme_get_setting('list_region');
			}
			if( wpjam_theme_get_setting('sidebar_left') ){
				if( $list_region == 'list' || $list_region == 'noimg_list' || $list_region == 'col_3_sidebar' ){ get_sidebar(); }
			}
		?>
		<div class="<?php if( $list_region == 'list' || $list_region == 'noimg_list' || $list_region == 'col_3_sidebar' ) { echo 'col-lg-9'; }else{ echo 'col-lg-12'; }?>">
			<?php
			$cat_banner_type_2 = $cat_banner_type == '2';
			if(!$cat_banner_type_2 && is_category() || is_tag() || is_tax()){ ?>
			<div class="term-bar">
				<div class="term-info">
					<span>当前<?php if(is_category()){ echo '分类'; }elseif(is_tag()){ echo '标签'; }else{ echo '浏览'; } ?></span>
					<h1 class="term-title"><?php single_term_title(); ?></h1>
				</div>
			</div>
			<?php }elseif(is_search()){ ?>
			<div class="term-bar">
				<div class="term-info">
					<span>搜索结果</span>
					<h1 class="term-title">
					“<?php echo $s; ?>” <?php global $wp_query; echo '搜到 ' . $wp_query->found_posts . ' 篇文章';?>
					</h1>
				</div>
			</div>
			<?php }elseif(is_author()){?>
			<div class="term-bar">
				<div class="author-image">
					<?php echo get_avatar( get_the_author_meta('ID'), '200' );?>
				</div>
				<div class="term-info">
					<h1 class="term-title" style="margin: 0 0 8px;"><?php echo get_the_author() ?></h1>
					<span><?php if(get_the_author_meta('description')){ echo the_author_meta( 'description' );}else{ echo '我还没有学会写个人说明！'; }?></span>
				</div>
			</div>
			<?php } ?>

			<div class="content-area">
				<main class="site-main">

				<?php if ( wpjam_theme_get_setting('post_list_ad') && !is_home() ){?>
					<div class="post-list-ad" style="margin-bottom:25px;width:100%;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;display: inline-block;overflow:hidden;">
						<?php echo wpjam_theme_get_setting('post_list_ad'); ?>
					</div>
				<?php }?>

				<?php
				//$list_region = wpjam_theme_get_setting('list_region');
				$list_region_list = $list_region == 'list';
				$list_region_noimg_list = $list_region == 'noimg_list';
				if(is_home() && wpjam_theme_get_setting('new_title')){ if( !$list_region_list && !$list_region_noimg_list ){?><h3 class="section-title"><span>最新文章</span></h3><?php } }?>
				<?php if(have_posts()){ ?>
					<div class="row<?php if( $list_region_list || $list_region_noimg_list ){?>none<?php }?> posts-wrapper">
					<?php if($list_region_list || $list_region_noimg_list){ if( is_home() && wpjam_theme_get_setting('new_title') ){ ?><h3 class="section-title"><span>最新文章</span></h3>
					<?php } }?>
					<?php while(have_posts()){ the_post(); ?>
						<?php get_template_part('template-parts/content-list'); ?>
					<?php } ?>
					<?php get_template_part( 'template-parts/paging' ); ?>
					</div>
				<?php }else{?>	
									
					<div class="_404">
						<?php if(is_search()){ ?>
						<h2 class="entry-title">姿势不对？换个词搜一下~</h2>
						<div class="entry-content">
							抱歉，没有找到“<?php echo $s; ?>”的相关内容
						</div>
						<?php } elseif(is_404()) { ?>
						<h1 class="entry-title">抱歉，这个页面不存在！</h1>
						<div class="entry-content">
							它可能已经被删除，或者您访问的URL是不正确的。也许您可以试试搜索？
						</div>
						<?php }else{?>
						<h1 class="entry-title">暂无文章</h1>
						<?php } ?>
						<?php if(is_search() || is_404()){ ?>
						<form method="get" class="search-form inline" action="<?php bloginfo('url'); ?>">
							<input class="search-field inline-field" placeholder="输入关键词进行搜索…" autocomplete="off" value="" name="s" required="true" type="search">
							<button type="submit" class="search-submit"><i class="iconfont icon-sousuo"></i></button>
						</form>
						<?php } ?>
					</div>
					<?php } ?>
				
				</main>
			</div>
		</div>
		<?php
			if( !wpjam_theme_get_setting('sidebar_left') ){
				if( $list_region == 'list' || $list_region == 'noimg_list' || $list_region == 'col_3_sidebar' ){ get_sidebar(); }
			}
		?>
	</div>
</div>
<?php get_footer();?>