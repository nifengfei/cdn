<?php
add_filter('wpjam_pages', function ($wpjam_pages) {

	if(get_current_blog_id() == WPJAM_SHOP_COMPONENT_BLOG_ID){
		return $wpjam_pages;
	}

	global $plugin_page;

	$template_type	= wpjam_shop_get_template_type();

	$wpjam_pages['shop-overview'] = [
		'menu_title' => '店铺概况',
		'icon'       => 'dashicons-dashboard',
		'position'   => 1,
		'capability' => 'view_shop',
		'subs'       => [
			'shop-overview'     => [
				'menu_title' => '店铺概况',
				'function'   => 'dashboard',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-overview.php',
			]
		],
	];

	if(is_multisite() && WPJAM_SHOP_COMPONENT_BLOG_ID){
		$wpjam_pages['shop-overview']['subs']['shop-announcement']	= [
			'menu_title' => '系统公告',
			'capability' => 'view_shop',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-announcement.php',
		];
	}

	$wpjam_pages['shop-name'] = [
		'menu_title' => '店铺管理',
		'icon'       => 'dashicons-store',
		'position'   => 2,
		'function'   => 'option',
		'capability' => 'view_shop',
		'subs'       => [
			'shop-name'    => [
				'menu_title' => '店铺设置',
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-setting.php'
			],
			'shop-home'    => [
				'menu_title' => '店铺装修',
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-home.php'
			],
			'shop-web'    => [
				'menu_title' => '网页版装修',
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-web.php'
			],
			'shop-staffs'  => [
				'menu_title' => '角色管理',
				'function'   => 'list',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-staffs.php',
			],
			'shop-logistics'     => [
				'menu_title' => '物流设置',
				'capability' => 'view_shop',
				'function'   => 'tab',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-logistics.php'
			],
			'shop-electronic-sheet'     => [
				'menu_title' => '电子面单',
				'capability' => 'view_shop',
				'function'   => 'tab',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-electronic-sheet.php'
			],
			'shop-pay'     => [
				'menu_title' => '支付设置',
				'capability' => 'manage_shop_administrator',
				'function'   => 'tab',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-pay.php'
			],
			'shop-limit'   => [
				'menu_title'  => '上限设置',
				'capability'  => 'manage_sites',
				'function'    => 'option',
				'page_file'   => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-limit.php',
				'option_name' => 'wpjam_count_limit',
			],
			'shop-qrcodes' => [
				'menu_title'  => '二维码和路径',
				'option_name' => 'shop_setting',
				'capability'  => 'view_shop',
				'page_file'   => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-qrcodes.php',
			],
		],
	];

	if(!WPJAM_ShopExtend::web()){
		unset($wpjam_pages['shop-name']['subs']['shop-web']);
	}

	if(!WPJAM_ShopExtend::store_card()){
		unset($wpjam_pages['shop-name']['subs']['shop-electronic-sheet']);
	}

//	if(!wpjam_current_shop_has('electronic_sheet')){
//		unset($wpjam_pages['shop-name']['subs']['shop-electronic-sheet']);
//	}

	if(!is_multisite()){
		unset($wpjam_pages['shop-name']['subs']['shop-staffs']);
	}

	if($template_type == 'city_delivery'){
		// unset($wpjam_pages['shop-name']['subs']['shop-home']);
		unset($wpjam_pages['shop-name']['subs']['shop-qrcodes']);

		$wpjam_pages['shop-tables'] = [
			'menu_title' => '桌码管理',
			'icon'       => 'dashicons-screenoptions',
			'position'   => '3.111',
			'function'   => 'option',
			'capability' => 'view_shop',
			'subs'       => [
				// 'shop-table-orders'    => [
				// 	'menu_title' => '订单',
				// 	'function'   => 'list',
				// 	'capability' => 'view_shop',
				// 	'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-table-orders.php',
				// ],
				'shop-tables'    => [
					'menu_title' => '餐桌',
					'function'   => 'list',
					'capability' => 'view_shop',
					'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-tables.php',
				]
			]
		];
	}elseif($template_type == 'gift_card'){
		unset($wpjam_pages['shop-name']['subs']['shop-home']);
	}elseif($template_type == 'warehouse'){
		$wpjam_pages['shop-name']['menu_title']	= '线下店铺';
		unset($wpjam_pages['shop-name']['subs']['shop-home']);
		unset($wpjam_pages['shop-name']['subs']['shop-qrcodes']);
		unset($wpjam_pages['shop-name']['subs']['shop-logistics']);

		// unset($wpjam_pages['shop-overview']['subs']['shop-announcement']);

		// wpjam_array_push($wpjam_pages['shop-name']['subs'], $wpjam_pages['shop-overview']['subs'], 'shop-staffs');

		// unset($wpjam_pages['shop-overview']);
	}

	$wpjam_pages['shop-orders'] = [
		'menu_title' => '订单管理',
		'icon'       => 'dashicons-cart',
		'capability' => 'view_shop_orders',
		'function'   => 'list',
		'position'   => '59.1',
		'subs'       => [
			'shop-orders'       => [
				'menu_title' => '订单管理',
				'function'   => 'list',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-orders.php',
			],
			'shop-selflifting-orders'       => [
				'menu_title' => '自提订单',
				'function'   => 'list',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-orders.php',
			],
			'shop-home-delivery-orders'       => [
				'menu_title' => '送货上门',
				'function'   => 'list',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-orders.php',
			],
			'shop-groupons'     => [
				'menu_title' => '拼团订单',
				'function'   => 'list',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-groupons.php',
			],
			'shop-gifts'     => [
				'menu_title' => '礼品订单',
				'function'   => 'list',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-gifts.php',
			],
			'shop-print-sheet-orders' => [
				'menu_title' => '面单打印',
				'function'   => 'list',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-orders.php',
			],
			'shop-bulk-consign' => [
				'menu_title' => '批量发货',
				'function'   => 'wpjam_shop_bulk_consign_page',
				'capability' => 'view_shop_orders',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-bulk-consign.php',
			],
		],
	];

	if(!wpjam_current_shop_has('electronic_sheet')){
		unset($wpjam_pages['shop-orders']['subs']['shop-print-sheet-orders']);
	}

	if($template_type == 'gift_card' || $template_type == 'city_delivery' || $template_type == 'warehouse'){
		unset($wpjam_pages['shop-orders']['subs']['shop-groupons']);
	}

	if($template_type != 'gift_card'){
		unset($wpjam_pages['shop-orders']['subs']['shop-gifts']);
	}

	if(!WPJAM_ShopExtend::self_lifting()){
		unset($wpjam_pages['shop-orders']['subs']['shop-selflifting-orders']);
	}

	if(!WPJAM_ShopExtend::home_delivery()){
		unset($wpjam_pages['shop-orders']['subs']['shop-home-delivery-orders']);
	}

	if(!in_array($template_type, ['gift_card', 'mono-groupon', 'warehouse'])){
		$extend_subs	= [
			'shop-extends'              => [
				'menu_title'  => '营销扩展',
				'capability'  => 'view_shop',
				'function'    => 'option',
				'option_name' => 'shop_extends',
				'page_file'   => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-extends.php',
			],
			'shop-vendors'              => [
				'menu_title' => '渠道管理',
				'function'   => 'list',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-vendors.php',
			],
			'shop-coupons'              => [
				'menu_title' => '优惠券',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-coupons.php',
				'function'   => 'tab'
			]
		];

		if (WPJAM_ShopExtend::coin()) {
			$extend_subs['shop-coin'] = [
				'menu_title' => wpjam_shop_get_platform_setting('coin_name'),
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-coin.php',
			];
		}

		if (WPJAM_ShopExtend::membership()) {
			$extend_subs['shop-membership'] = [
				'menu_title' => '会员',
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-membership.php',
			];
		}

		if (WPJAM_ShopExtend::store_card()) {
			$extend_subs['shop-store-card'] = [
				'menu_title' => '储值卡',
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-store-card.php',
			];
		}

		if(!wpjam_current_shop_has('store_card')){
			unset($extend_subs['shop-store-card']);
		}

		if(wpjam_current_shop_has('seckill')){
			$extend_subs['shop-seckill'] 	= [
				'menu_title' => '秒杀',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-seckill.php',
				'function'	 => 'tab'
			];
		}

		if(WPJAM_ShopExtend::affiliate()) {
			$extend_subs['shop-affiliate'] = [
				'menu_title' => '分享家计划',
				'function'   => 'tab',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-affiliate.php',
			];
		}

		if (WPJAM_ShopExtend::red_packet_newbie()) {
			$extend_subs['shop-red-packet-newbie'] = [
				'menu_title'      => '新人红包',
				'function'        => 'list',
				'list_table_name' => 'shop_coupons',
				'capability'      => 'view_shop',
				'page_file'       => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-coupons.php',
			];
		}

		if (WPJAM_ShopExtend::red_packet_share()) {
			$extend_subs['shop-red-packet-share'] = [
				'menu_title' => '分享红包',
				'function'   => 'list',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-red-packets.php',
			];
		}

		if (WPJAM_ShopExtend::crowd_pay()) {
			$extend_subs['shop-crowd-pay'] = [
				'menu_title' => '订单代付',
				'function'   => 'option',
				'capability' => 'view_shop',
				'option_name'=>'shop_setting',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-crowd-pay.php',
			];
		}

		if (WPJAM_ShopExtend::order_paid_notify()) {
			$extend_subs['shop-order-notify'] = [
				'menu_title' => '订单通知',
				'function'   => 'option',
				'capability' => 'view_shop',
				'option_name'=>'shop_setting',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-order-notify.php',
			];
		}

		if (WPJAM_ShopExtend::order_annotation()) {
			$extend_subs['shop-order-annotation'] = [
				'menu_title' => '订单留言',
				'function'   => 'option',
				'capability' => 'view_shop',
				'option_name'=>'shop_order_annotation',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-order-annotation.php',
			];
		}

		if(is_multisite() && WPJAM_SHOP_COMPONENT_BLOG_ID){
			$extend_subs['shop-groupon-introduction'] = [
				'menu_title' => '普通拼团',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-introduction.php',
				'function'	 => 'wpjam_shop_help_page'
			];

			$extend_subs['shop-miaosha-introduction'] = [
				'menu_title' => '限时购',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-introduction.php',
				'function'	 => 'wpjam_shop_help_page'
			];

			$extend_subs['shop-customservice'] 	= [
				'menu_title' => '客服说明',
				'capability' => 'view_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-introduction.php',
				'function'	 => 'wpjam_shop_help_page'
			];
		}

		$wpjam_pages['shop-extends'] = [
			'menu_title' => '营销中心',
			'icon'       => 'dashicons-filter',
			'position'   => '59.2',
			'capability' => 'view_shop',
			'subs'       => $extend_subs
		];
	}

	if(is_multisite()){
		$vip_all	= wpjam_shop_get_platform_vip_setting('vip_all');

		if(!$vip_all){
			$vip_subs	= [];

			$vip_subs['shop-vip']	= [
				'menu_title'	=> '专业版',
				'capability'	=> 'manage_shop',
				'page_file'		=> WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-vip.php',
			];

			if(current_user_can('manage_sites')){
				$vip_subs['shop-partner-blogs']	= [
					'menu_title'	=> '店铺管理',
					'capability'	=> 'manage_shop',
					'function'		=> 'list',
					'page_file'		=> WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-partners.php',
				];

				$vip_subs['shop-partner-apply']	= [
					'menu_title'	=> '申请合伙人',
					'capability'	=> 'manage_shop',
					'page_file'		=> WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-introduction.php',
					'function'		=> 'wpjam_shop_help_page'
				];
			}else{
				$is_partner	= wpjam_shop_is_partner();

				if($is_partner){
					$vip_subs['shop-partner-blogs']	= [
						'menu_title'		=> '合伙人店铺',
						'capability'		=> 'manage_shop',
						'function'			=> 'list',
						'list_table_name'	=> 'shop_partner_blogs',
						'page_file'			=> WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-partners.php',
					];
				}else{
					$vip_subs['shop-partner-apply']	= [
						'menu_title'	=> '申请合伙人',
						'capability'	=> 'manage_shop',
						'page_file'		=> WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-introduction.php',
						'function'		=> 'wpjam_shop_help_page'
					];
				}
			}

			$wpjam_pages['shop-vip']   = [
				'menu_title'	=> '专业版',
				'capability'	=> 'manage_shop',
				'icon'			=> 'dashicons-smiley',
				'position'		=> '59.3',
				'function'		=> 'tab',
				'subs'			=> $vip_subs
			];
		}
	}

	if($template_type != 'warehouse'){
		$wpjam_pages['shop-users'] = [
			'menu_title' => '用户画像',
			'icon'       => 'dashicons-admin-users',
			'position'   => '59.5',
			'function'   => 'list',
			'capability' => 'view_shop_weapp_users',
			'subs'       => [
				'shop-users'                  => [
					'menu_title' => '用户管理',
					'function'   => 'list',
					'capability' => 'view_shop_weapp_users',
					'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-users.php',
				],
				'shop-user-tags'              => [
					'menu_title' => '用户标签',
					'function'   => 'list',
					'capability' => 'view_shop_weapp_users',
					'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-users.php',
				],
				'shop-user-tag-relationships' => [
					'menu_title' => '标签记录',
					'function'   => 'list',
					'capability' => 'view_shop_weapp_users',
					'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-users.php',
				],
				// 'shop-masssends'              => [
				// 	'menu_title' => '群发通知',
				// 	'function'   => 'list',
				// 	'capability' => 'view_shop',
				// 	'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-masssends.php',
				// ]
				// 'shop-credits'	=>[
				// 	'menu_title'	=> '积分管理',
				// 	'function'	=> 'wpjam_nonono_page',
				// 	'capability'	=> 'view_shop_weapp_users',
				// 	'page_file'	=> WPJAM_SHOP_PLUGIN_DIR .'admin/pages/nonono.php'
				// ],
			],
		];
	}

	if($template_type == 'magua'){
		$wpjam_pages['shop-users']['subs']['shop-dedicated-managers']	= [
			'menu_title' => '专属客服',
			'function'   => 'list',
			'capability' => 'view_shop',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-dedicated-managers.php',
		];
	}

	$wpjam_pages['shop-stats'] = [
		'menu_title' => '数据中心',
		'icon'       => 'dashicons-chart-bar',
		'position'   => '59.6',
		'capability' => 'view_shop_stats',
		'subs'       => [
			'shop-stats'  => [
				'menu_title' => '数据中心',
				'function'   => 'tab',
				'capability' => 'view_shop_stats',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-stats.php',
			],
			// 'shop-stats-es'  => [
			// 	'menu_title' => '数据中心ES',
			// 	'function'   => 'tab',
			// 	'capability' => 'view_shop_stats',
			// 	'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-stats-es.php',
			// ],
			'shop-stats2' => [
				'menu_title' => '所有店铺',
				'function'   => 'tab',
				'capability' => 'manage_sites',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-stats2.php'
			],
			'shop-stats3' => [
				'menu_title' => '开店数据',
				'capability' => 'manage_sites',
				'function'   => 'wpjam_blog_stats_page',
				'page_file'  => WPJAM_DEBUG_PLUGIN_DIR . 'admin/pages/wpjam-blog-stats.php',
			],
		],
	];

	$template_type	= wpjam_shop_get_template_type();
	if(is_multisite() && $template_type != 'warehouse'){
		$wpjam_pages['shop-grant'] = [
			'menu_title' => '开放平台',
			'capability' => 'view_shop',
			'icon'       => 'dashicons-hammer',
			'position'   => '59.9',
			'subs'       => [
				'shop-grant' => [
					'menu_title' => '开发平台',
					'capability' => 'view_shop',
					'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-grant.php',
				],
				'shop-open'  => [
					'menu_title' => '开发文档',
					'capability' => 'view_shop',
					'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-open.php',
					'function'   => 'tab'
				],
			],
		];
	}

	if($template_type == 'gift_card'){
		$wpjam_pages['products']['subs']['shop-gift_card-groups'] = [
			'menu_title' => '礼品卡分组',
			'capability' => 'view_shop',
			'function'   => 'option',
			'option_name'=> 'shop_gift_card_groups',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-gift_card-groups.php',
		];

	}else{
		if($template_type == 'city_delivery'){
			unset($extend_subs['shop-groupon-introduction']);
			unset($extend_subs['shop-miaosha-introduction']);
		}

		if (isset($plugin_page) &&  $plugin_page == 'shop-article-cubes') {
			// if (empty($_GET['post_id']) || !get_post($_GET['post_id'])) {
			// 	wp_die('请先选择文章，在设置图片魔方。');
			// }

			$wpjam_pages['articles']['subs']['shop-article-cubes'] = [
				'menu_title' => '图片魔方',
				'capability' => 'view_shop',
				'function'   => 'list',
				'query_args' => ['post_id'],
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-article-cubes.php',
			];
		}

		$wpjam_pages['articles']['subs']['shop-article-replies'] = [
			'menu_title' => '文章评论',
			'capability' => 'view_shop',
			'function'   => 'list',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-article-replies.php',
		];

		$wpjam_pages['articles']['subs']['shop-article-setting'] = [
			'menu_title' => '文章设置',
			'capability' => 'view_shop',
			'function'   => 'option',
			'option_name'=> 'shop_article',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-article-setting.php',
		];

		if (isset($plugin_page) &&  $plugin_page == 'shop-product-code') {
			$wpjam_pages['products']['subs']['shop-product-code'] = [
				'menu_title' => '新增商品',
				'capability' => 'manage_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-product-code.php',
			];
		}

		if($template_type == 'warehouse'){
			$wpjam_pages['products']['subs']['shop-stocks'] = [
				'menu_title' => '库存管理',
				'function'   => 'tab',
				'capability' => 'manage_shop',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-stocks.php',
			];

			$wpjam_pages['products']['subs']['shop-product-import'] = [
				'menu_title' => '商品导入',
				'capability' => 'manage_shop',
				'function'   =>'wpjam_shop_product_import_page',
				'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-product-import.php',
			];
		}

		$wpjam_pages['products']['subs']['shop-product-replies'] = [
			'menu_title' => '商品评价',
			'capability' => 'view_shop',
			'function'   => 'list',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-product-replies.php',
		];
	}

	if(wpjam_current_shop_has('chuangkit')){
		$wpjam_pages['media']['subs']['chuangkit'] = [
			'menu_title' => '创客贴',
			'capability' => 'view_shop',
			'page_file'  => WPJAM_SHOP_PLUGIN_DIR . 'admin/pages/shop-chuangkit.php',
		];
	}


	return $wpjam_pages;
});


add_action('admin_menu',function () {
	global $menu, $submenu;

	global $menu, $submenu;
	$menu['59.4']	= ['',	'read',	'separator'.'59.4', '', 'wp-menu-separator'];

	remove_menu_page('edit-comments.php');

	if (isset($submenu['upload.php'])) {
		$menu[10][0]                  = $submenu['upload.php'][5][0] = '素材管理';
		$submenu['upload.php'][10][0] = '添加素材';
	}

	if(wpjam_shop_get_template_type() == 'warehouse'){
		remove_submenu_page('edit.php?post_type=product', 'post-new.php?post_type=product');

		add_action('load-post-new.php', function (){
			wp_redirect(admin_url('edit.php?post_type=product&page=shop-product-code'));
			exit;
		});
	}

	remove_menu_page('index.php');
	remove_menu_page('edit.php');
	remove_menu_page('edit.php?post_type=page');

	if (!current_user_can('manage_sites')) {
		remove_menu_page('weapp');
		remove_menu_page('index.php');
		remove_menu_page('tools.php');
		remove_menu_page('options-general.php');
		remove_menu_page('themes.php');

		remove_menu_page('wpjam-basic');
		remove_menu_page('wpjam-debug');
		remove_menu_page('wpjam-devices');

		if(is_multisite()){
			remove_menu_page('users.php');
			remove_submenu_page('users.php', 'user-new.php');
		}
	}

	if (isset($submenu['shop-customservice'])) {
		$submenu['shop-customservice'][1] = [
			'官方客服工具',
			'view_shop_customservice',
			'https://mpkf.weixin.qq.com/',
			'官方客服工具',
		];
	}
});


