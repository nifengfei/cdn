<?php
if($action == 'weixin-bind'){
    if(isset($_GET['unbind'])){
        wpjam_weixin_unbind($user_id);
    }elseif($_SERVER['REQUEST_METHOD'] == 'POST'){

        $scene  = $_POST['scene'] ?? '';
        $code   = $_POST['code'] ?? '';

        $openid = wpjam_verify_weixin_qrcode($scene, $code);

        if(is_wp_error($openid)){
            $errors = $openid;
        }else{
            $user_id = get_current_user_id();
            $user    = wpjam_weixin_bind($user_id, $openid);

            if(is_wp_error($user)){
                $errors = $user;
            }
        }
    }
//}elseif($action == 'add-topic' || $action == 'topic'){
}elseif($action == 'topic'){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
     include WPJAM_TOPIC_PLUGIN_DIR.'admin/topics.php';
        $title          = $_POST['topic_title'];
        $raw_content    = $_POST['topic_content'];
        $group_id       = $_POST['group_id'];

        $data   = compact('title', 'raw_content', 'group_id');

        $topic_id = WPJAM_Topic::insert($data);

        if(is_wp_error($topic_id)){
            $errors     = $topic_id;
        }else{
            wp_safe_redirect(home_url('topic/'.$topic_id));
            exit;
        }
    }
}

$action_file = get_template_directory().'/user/action/'.$action.'.php';

if(!is_file($action_file)){
    include(get_template_directory().'/404.php');
    exit;
}

get_header();
?>

<div class="user site-content container">
    <div class="row">

        <div class="col-lg-3">
            <aside class="user-widget widget-area">

                <div class="sidebar-header header-cover" style="background-image: url(<?php $login_bg_img = wpjam_theme_get_setting('login_bg_img') ?: get_template_directory_uri().'/static/images/login_bg_img.jpg'; echo $login_bg_img;?>);">
                    <div class="sidebar-image">
                        <img src="<?php echo get_avatar_url(get_current_user_id());?>">
                        <a style="font-size: 18px;font-weight: 700;color: #fff;" href="<?php echo home_url(user_trailingslashit('/user'));?>"><?php echo $current_user->nickname;?></a>
                    </div>
                    <p class="sidebar-brand"><?php if( $current_user->description ){ echo $current_user->description;}else{ echo '我还没有学会写个人说明！'; }?></p>
                </div>

                <section class="widget widget_categories">
                    <h5 class="widget-title">用户中心</h5>
                    <ul>

                        <?php if( current_user_can( 'manage_options' ) ) {?>
                            <li><a href="<?php echo home_url(user_trailingslashit('/wp-admin')); ?>"><span class="iconfont icon-yibiaopan1"></span> 进入后台</a></li>
                        <?php }?>
                        <?php if( !wpjam_theme_get_setting('subscriber_ft') || !current_user_can('subscriber') ){?>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/contribute')); ?>"><?php if($action == 'contribute') echo '<i></i>';?><span class="iconfont icon-ykq_tab_tougao"></span> 文章投稿</a></li>
                        <?php }?>
                        <?php if(wpjam_theme_get_setting('add_topic')) {?>
                            <?php if( !wpjam_theme_get_setting('subscriber_ft') || !current_user_can('subscriber') ){?>
                            <li><a href="<?php echo home_url(user_trailingslashit('/user/topic')); ?>"><?php if($action == 'topic') echo '<i></i>';?><span class="iconfont icon-fatieliang" style="font-size: 17px;vertical-align: top;"></span> 发布帖子</a></li>
                            <?php }?>
                        <?php }?>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/posts')); ?>"><?php if($action == 'posts') echo '<i></i>';?><span class="iconfont icon-wenzhang"></span> 我的文章</a></li>
                        <?php if(wpjam_theme_get_setting('single_fav')){?>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/collection')); ?>"><?php if($action == 'collection') echo '<i></i>';?><span class="iconfont icon-collection"></span> 我的收藏</a></li>
                        <?php }?>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/comments')); ?>"><?php if($action == 'comments') echo '<i></i>';?><span class="iconfont icon-pinglun"></span> 我的评论</a></li>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/profile')); ?>"><?php if($action == 'profile') echo '<i></i>';?><span class="iconfont icon-zhanghaoxinxi"></span> 账号信息</a></li>
                        <?php $login_actions = wpjam_get_login_actions('bind'); if($login_actions && isset($login_actions['weixin'])){?>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/weixin-bind')); ?>"><?php if($action == 'weixin-bind') echo '<i></i>';?><span class="iconfont icon-weixin3"></span> 绑定微信</a></li>
                        <?php }?>
                        <li><a href="<?php echo home_url(user_trailingslashit('/user/password')); ?>"><?php if($action == 'password') echo '<i></i>';?><span class="iconfont icon-xiugaimima"></span> 修改密码</a></li>
                        <li><a href="<?php echo wp_logout_url( home_url() ); ?>"><span class="iconfont icon-tuichudenglu"></span> 退出登录</a></li>
                    </ul>
                </section>
            </aside>
        </div>

        <?php  include($action_file);?>

       </div>
    </div>
<?php get_footer();?>