<?php
if(!defined('ABSPATH')){
	include('../../../../../wp-config.php');
	
	if (!current_user_can('manage_sites')) {
	    echo 'hehe';
	    return;
	}

	require_once WPJAM_SHOP_PLUGIN_DIR . 'public/upgrade/utils.php';

	$offset = get_transient('wpjam_shop_post_offset');
	$offset = $offset?:0;

	$weapps	= $wpdb->get_results("SELECT * FROM wp_weapps where component_blog_id >0 OR blog_id = 339 ORDER BY blog_id DESC LIMIT 100 OFFSET $offset");
	if ($weapps) {
	    $offset += 100;

	    echo $offset."<br />";

	    set_transient('wpjam_shop_post_offset', $offset, HOUR_IN_SECONDS);

	    foreach ($weapps as $weapp) {
	        $_blog_id = $weapp->blog_id;
	        
	        switch_to_blog($_blog_id);

	        $table	= $wpdb->posts;

			if(!$wpdb->query("SHOW COLUMNS FROM `{$table}` WHERE field='platform'")){
				$wpdb->query("ALTER TABLE `{$table}` ADD COLUMN platform int(10) NOT NULL DEFAULT 0");
				$wpdb->query("ALTER TABLE `{$table}` ADD KEY `platform_idx` (`platform`);");
			}

			$table	= WPJAM_PostSKU::get_table();

			if(!$wpdb->query("SHOW COLUMNS FROM `{$table}` WHERE field='type'")){
				$wpdb->query("ALTER TABLE `{$table}` ADD COLUMN type int(1) NOT NULL DEFAULT 0");
			}

			if(!$wpdb->query("SHOW INDEX FROM `{$table}` WHERE Key_name='type_idx'")){
				$wpdb->query("ALTER TABLE `{$table}` ADD KEY `type_idx` (`type`);");
			}

	        $_query = new WP_Query(
				[
					'posts_per_page' => -1,
					'post_type'      => 'product',
					'post_status'    => ['publish', 'pending', 'draft', 'future', 'trash', 'sold_out', 'unpublished'],
				]
			);

			if ($_query->posts) {
				foreach ($_query->posts as $post) {
					$post_id = $post->ID;

					if (!metadata_exists('post', $post_id, 'promotion_type')) {
						if(get_post_meta($post_id, 'groupon_enable', true)){
							$promotion_type	= 'groupon_enable';
						}elseif(get_post_meta($post_id, 'miaosha_enable', true)){
							$promotion_type	= 'miaosha_enable';
						}elseif(get_post_meta($post_id, 'bargain_enable', true)){
							$promotion_type	= 'bargain_enable';
						}elseif(get_post_meta($post_id, 'seckill_enable', true)){
							$promotion_type	= 'seckill_enable';
						}elseif(get_post_meta($post_id, 'membership_price_enable', true)){
							$promotion_type = 'membership_price_enable';
						}elseif(get_post_meta($post_id, 'membership_dedicated_enable', true)){
							$promotion_type = 'membership_dedicated_enable';
						}else{
							$promotion_type = 0;
						}

						WPJAM_Product::set_promotion_type($post_id, $promotion_type);
					}

					$sku_model = wpjam_shop_get_sku_model($post_id);
					$sku	= $sku_model::get_sku($post_id);
					if(!$sku){
						if (metadata_exists('post', $post_id, 'stock')) {
						 	$sku_model::add_main_record($post_id);
						}
					}

					if($sku && metadata_exists('post', $post_id, 'price')){
						// delete_post_meta($post_id, 'product_no');
						// delete_post_meta($post_id, 'price');
						// delete_post_meta($post_id, 'cost');
						// delete_post_meta($post_id, 'stock');
						// delete_post_meta($post_id, 'sales');
						// delete_post_meta($post_id, 'discount');
					}
				}
			}

	        restore_current_blog();

	        echo "<br />";
	    } ?>
		<script language="JavaScript"> 
		setTimeout(function(){window.location.reload()},1000);  //指定1秒刷新一次 
		</script> 
		<?php
	    exit;
	} else {
	    echo '升级完成!';
	    exit;
	}
}else{
	require_once WPJAM_SHOP_PLUGIN_DIR . 'public/upgrade/utils.php';
	global $wpdb;

	$table	= $wpdb->posts;

	if(!$wpdb->query("SHOW COLUMNS FROM `{$table}` WHERE field='platform'")){
		$wpdb->query("ALTER TABLE `{$table}` ADD COLUMN platform int(10) NOT NULL DEFAULT 0");
		$wpdb->query("ALTER TABLE `{$table}` ADD KEY `platform_idx` (`platform`);");
	}

	$table	= WPJAM_PostSKU::get_table();

	if(!$wpdb->query("SHOW COLUMNS FROM `{$table}` WHERE field='type'")){
		$wpdb->query("ALTER TABLE `{$table}` ADD COLUMN type int(1) NOT NULL DEFAULT 0");
	}

	if(!$wpdb->query("SHOW INDEX FROM `{$table}` WHERE Key_name='type_idx'")){
		$wpdb->query("ALTER TABLE `{$table}` ADD KEY `type_idx` (`type`);");
	}
}

	