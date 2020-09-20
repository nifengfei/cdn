<?php

//文章加载 主循环
function wpjam_loadmore_ajax_handler(){
	$query_args = wpjam_json_decode(stripslashes($_POST['query']));

	$query_args['paged'] = $_POST['paged'] + 1; // we need next page to be loaded
	$query_args['post_status'] = 'publish';
	$query_args['ignore_sticky_posts'] = 1;
	
	global $wp;

	foreach($query_args as $key => $value) {
		$wp->set_query_var($key, $value);
	}

	$wp->query_posts();
 
	if(have_posts()){
		while(have_posts()){ the_post();
			get_template_part( 'template-parts/content-list' );
		}
	}

	exit; 
}
add_action('wp_ajax_loadmore', 'wpjam_loadmore_ajax_handler');
add_action('wp_ajax_nopriv_loadmore', 'wpjam_loadmore_ajax_handler');

//收藏
function wpjam_post_fav_ajax_handler(){  
    global $wpdb, $post;  
    if($post_id = $_POST["post_id"]){
        $user_id   = get_current_user_id();
        
        WPJAM_Comment::action($user_id, $post_id, 'fav');

        echo get_post_meta($post_id, 'favs', true); 
    }

    exit;  
} 
add_action('wp_ajax_post_fav', 'wpjam_post_fav_ajax_handler');

//取消收藏
function wpjam_post_unfav_ajax_handler(){
    global $wpdb, $post;
    if($post_id = $_POST["post_id"]){
        $user_id   = get_current_user_id();

        WPJAM_Comment::action($user_id, $post_id, 'unfav');

        echo get_post_meta($post_id, 'favs', true);
    }

    exit;
} 
add_action('wp_ajax_post_unfav', 'wpjam_post_unfav_ajax_handler');

/* 收藏按钮 */
function wpjam_post_fav_button($post_id){

    $fav_count = get_post_meta($post_id, 'favs', true) ?: '0';

    $user_id = get_current_user_id();
    if(!empty($user_id)){

        //判断是否已经收藏
        $favs  = WPJAM_Comment::get_comments(['post_id'=>$post_id, 'type'=>'fav']);
        $is_faved = 0;

        if($user_id && $favs){
            $user_ids = wp_list_pluck($favs, 'user_id');
            if($user_ids && in_array($user_id, $user_ids)){
                $is_faved = 1;
            }
        }

        if ( $is_faved ){ //get_comments(['post_id'=>$post_id, 'user_id'=>$user_id, 'type'=>'fav'])
            echo '<a href="javascript:;" data-id="'.$post_id.'" class="fav unfav" title="已收藏，点击取消"><i class="iconfont icon-collection"></i> <span class="fav-text">收藏</span><span class="count">'.$fav_count.'</span></a>';
        }else{
            echo '<a href="javascript:;" data-id="'.$post_id.'" class="fav" title="点击收藏"><i class="iconfont icon-collection"></i> <span class="fav-text">收藏</span><span class="count">'.$fav_count.'</span></a>';
        }
    }else{
        $onclick = "alert('您必须登录后才能收藏')";
        echo '<a href="'.home_url(user_trailingslashit('/user/login')).'" rel="nofollow" target="_blank" class="login-fav" title="您必须登录后才能收藏" onclick='.$onclick.'><i class="iconfont icon-collection"></i> 收藏<span class="count">'.$fav_count.'</span></a>';       
    }

}

//点赞按钮
function wpjam_post_like_button($post_id){
	$like_count    = get_post_meta($post_id, 'likes', true) ?: '0';
	$liked		   = isset($_COOKIE['liked_' . $post_id]) ? 'liked' : '';

	echo '<a href="javascript:;" data-id="'.$post_id.'" class="like '.$liked.'"><i class="iconfont icon-yixiangkan"></i><span class="count">'.$like_count.'</span></a>';
}

function wpjam_post_like_button_2($post_id){	
	$like_count    = get_post_meta($post_id, 'likes', true) ?: '0';
	$liked		   = isset($_COOKIE['liked_' . $post_id]) ? 'liked' : '';
	
	echo '<a href="javascript:;" data-id="'.$post_id.'" class="like '.$liked.'"><i class="iconfont icon-yixiangkan"></i> 赞<span class="count">'.$like_count.'</span></a>';
}

//点赞
function wpjam_post_like_ajax_handler(){  
	global $wpdb, $post;  
	if($post_id = $_POST["post_id"]){
		$like_count = get_post_meta($post_id, 'likes', true);  

		$expire = time() + 99999999;  
		$domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost  

		setcookie('liked_' . $post_id, $post_id, $expire, '/', $domain, false);  
		if (!$like_count || !is_numeric($like_count)){
			update_post_meta($post_id, 'likes', 1);
		}else{
			update_post_meta($post_id, 'likes', ($like_count + 1));
		};  

		echo get_post_meta($post_id, 'likes', true); 
	}

	exit;  
}
add_action('wp_ajax_nopriv_post_like', 'wpjam_post_like_ajax_handler');  
add_action('wp_ajax_post_like', 'wpjam_post_like_ajax_handler');

//投稿
add_action( 'wp_ajax_publish_post' , function (){
	global $wpdb;
	$user_id		= get_current_user_id();
	$post_id		= sanitize_text_field($_POST['post_id']);
	$post_status	= sanitize_text_field($_POST['post_status']);
	$thumbnail		= sanitize_text_field($_POST['thumbnail']);

	if($post_id ){

		$old_post	= get_post($post_id);

		if($old_post->post_author != $user_id){
			$msg = array(
				'state' => 201,
				'tips' => '你不能编辑别人的文章。'
			); 
		}else{
			$post_arr	= [
				'ID'			=> $post_id,
				'post_title'	=> wp_strip_all_tags( $_POST['post_title'] ),
				'post_content'  => $_POST['editor'],
				'post_status'   => $post_status,
				'post_author'   => $user_id,
				'post_category' => $_POST['cats']
			];

			wp_update_post( $post_arr );
			set_post_thumbnail( $post_id, $thumbnail );

			if( $post_id && $thumbnail ){
				set_post_thumbnail( $post_id, $thumbnail );
			}

			$msg = array(
				'state'	=> 200,
				'tips'	=> '文章更新成功！',
				'url'	=> home_url(user_trailingslashit('/user'))
			);
		}
	}else{
		$post_arr	= [
			'post_title'	=> wp_strip_all_tags( $_POST['post_title'] ),
			'post_content'  => $_POST['editor'],
			'post_status'   => $post_status,
			'post_author'   => $user_id,
			'post_category' => $_POST['cats']
		];

		$post_id = wp_insert_post( $post_arr );

		if( $post_id && $thumbnail ){
			set_post_thumbnail( $post_id, $thumbnail );
		}

		if( $post_id ){
			$msg = array(
				'state'	=> 200,
				'tips'	=> '文章提交成功',
				'url'	=> home_url(user_trailingslashit('/user'))
			);
			add_post_meta($post_id, 'tg' , $user_id);
		}else{
			$msg = array(
				'state' => 201,
				'tips' => '提交失败，请稍候再试'
			);  
		}
	}

	wpjam_send_json($msg);
} );


//验证码程序
function vercode(){
    if( isset($_GET['vercode']) && $_GET['vercode'] == '1' ){
        include('vercode/code_char.php');
        //将生成的验证码写入session，备验证页面使用
        if(!isset($_SESSION)){
            session_start();
        }

        $_SESSION["verification_code"] = getCode(4,90,30);
        die();
    }
}
add_action('send_headers','vercode');

//前端Ajax注册
function xintheme_register(){

    if(!isset($_SESSION)){
        session_start();
    }

    if( isset($_POST['vercode']) && $_POST['vercode'] == $_SESSION['verification_code'] ){

        global $wpdb;

        $user_login = $_POST['username'];
        $email = $_POST['email'];
        $pwd_1 = $_POST['password_1'];
        $pwd_2 = $_POST['password_2'];

        if(empty($_POST['captcha']) || empty($_SESSION['WPJAM_XinTheme_autumn_captcha']) || trim(strtolower($_POST['captcha'])) != $_SESSION['WPJAM_XinTheme_autumn_captcha']){
            $msg = json_encode( 
                array(
                    'state' => 201,
                    'tips'  => '邮箱验证码错误'
                ) 
            ); 
        }else{
            $mail = "/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/";
            if( preg_match( $mail , $email ) ){ 

                if( email_exists( $email ) ){

                    $msg = json_encode( 
                        array(
                            'state' => 201,
                            'tips'  => 'E-mail已存在，请更换E-mail。'
                        ) 
                    ); 

                }elseif ( username_exists( $user_login ) ) {
                    $msg = json_encode( 
                        array(
                            'state' => 201,
                            'tips'  => '用户名已存在，请更换用户名。'
                        ) 
                    ); 
                }else{

                    //$max_id = $wpdb->get_var( "SELECT ID FROM $wpdb->users ORDER BY ID DESC LIMIT 0,1" );

                    if( $pwd_1 === $pwd_2 ){

                        //$user_login = $max_id.date('His',time()).$max_id+1;
                        $user_id = wp_create_user( $user_login, $pwd_1 , $email );

                        if( $user_id ){

                            $msg = json_encode( 
                                array(
                                    'state' => 200,
                                    'tips'  => '注册成功,正在跳转至用户中心！',
                                    'url'   => home_url(user_trailingslashit('/user'))
                                ) 
                            );
                            wp_set_current_user($user_id);
                            wp_set_auth_cookie($user_id);

                            unset($_SESSION['verification_code']);

                        }else{

                            $msg = json_encode( 
                                array(
                                    'state' => 201,
                                    'tips'  => '发生未知错误，请稍后再试'
                                ) 
                            );

                        }

                    }else{

                        $msg = json_encode( 
                            array(
                                'state' => 201,
                                'tips'  => '两次输入的密码不一致'
                            ) 
                        );

                    }

                }  

            }else{

                $msg = json_encode( 
                    array(
                        'state' => 201,
                        'tips'  => 'E-mail地址错误'
                        ) 
                );
            }
        }

    }else{
        $msg = json_encode( 
            array(
                'state' => 201,
                'tips'  => '图形验证码输入错误'
            ) 
        );        

    }

    echo $msg;
    die();

}
add_action( 'wp_ajax_nopriv_xintheme_register' , 'xintheme_register' );

//支持中文用户名注册
function xintheme_sanitize_user ($username, $raw_username, $strict) {
    $username = wp_strip_all_tags( $raw_username );
    $username = remove_accents( $username );
    $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
    $username = preg_replace( '/&.+?;/', '', $username ); 
    if ($strict) {
        $username = preg_replace ('|[^a-z\p{Han}0-9 _.\-@]|iu', '', $username);
    }
    $username = trim( $username );
    $username = preg_replace( '|\s+|', ' ', $username );
    return $username;
}
add_filter ('sanitize_user', 'xintheme_sanitize_user', 10, 3);

//前端Ajax登陆
function xintheme_login(){

    if(!isset($_SESSION)){
        session_start();
    }

    if( isset($_POST['vercode']) && $_POST['vercode'] == $_SESSION['verification_code'] ){

        $creds['user_login'] = $_POST['login_name'];
        $creds['user_password'] = $_POST['password'];
        $user = wp_signon( $creds );

        if ( is_wp_error($user) ) {
            $msg = json_encode(
                array( 
                    'state' => 201, 
                    "tips"  => '账号密码不匹配' 
                )
            );
        }
        else{
            $msg = json_encode( 
                array( 
                    'state' => 200, 
                    "tips"  => '嗨，欢迎回来！' , 
                    'url'   => home_url(user_trailingslashit('/user'))
                )
            );
        } 
    }else{
        $msg = json_encode( 
            array(
                'state'     => 201,
                'tips'      => '验证码不正确'
            ) 
        );        

    }

    echo $msg;
    die();
}
add_action( 'wp_ajax_nopriv_xintheme_login' , 'xintheme_login' );

//支持邮箱登陆
add_action('wp_authenticate',function (&$user_login) {
    if(is_email($user_login)){
        $user = get_user_by('email', $user_login);

        if(!empty($user->user_login)){
            $user_login = $user->user_login;
        }
    }
});

//找回密码 - 获取验证码
function get_email_vcode(){
    $login_name = sanitize_text_field($_POST['login_name']);
    $user_id = email_exists($login_name) ? email_exists($login_name) : username_exists($login_name);
    if( $user_id ){
        $user = get_userdata($user_id);
        if( send_vcode($user->data->user_email) ){
            $msg = array(
                'state' => 200,
                'tips' => '验证码发送成功',
            );
            $_SESSION["Get_pwd_user"] = $user_id;
            $_SESSION["Get_pwd_step"] = 2;
        }else{
            $msg = array(
                'state' => 201,
                'tips' => '验证码发送失败，请稍候再试',
            );
        }
    }else{
        $msg = array(
            'state' => 201,
            'tips' => '用户名或E-mail不存在'
        );
    }
    echo json_encode($msg);
    die();
}
add_action( 'wp_ajax_nopriv_get_email_vcode' , 'get_email_vcode');

//找回密码 - 验证码验证
function get_email_ver(){
    $vcode = sanitize_text_field($_POST['vcode']);
    if( $vcode == $_SESSION['vcode'] ){
        $msg = array(
            'state' => 200,
            'tips' => '验证成功'
        );
        $_SESSION["Get_pwd_step"] = 3;
    }else{
        $msg = array(
            'state' => 201,
            'tips' => '验证码不正确'
        );
    }
    echo json_encode($msg);
    die();
}
add_action( 'wp_ajax_nopriv_get_email_ver' , 'get_email_ver' );

//找回密码 - 密码修改
function get_email_pass(){
    if( $_SESSION["Get_pwd_step"] == 3 && $_SESSION["Get_pwd_user"] ){
        $newpwd = sanitize_text_field($_POST['pwd']);
        wp_set_password( $newpwd, $_SESSION["Get_pwd_user"] );
        $msg = array(
            'state' => 200,
            'tips'  => '密码修改成功',
            'url'   => home_url(user_trailingslashit('/user/login')),
            'html'  => '<h1>密码修改成功，正在为您跳转至<a href="'.home_url(user_trailingslashit('/user/login')).'">登录页面</a></h1>'
        );
        unset($_SESSION["Get_pwd_user"]);
        unset($_SESSION["Get_pwd_step"]);
        unset($_SESSION['vcode']);
    }else{
        $msg = array(
            'state' => 404,
            'tips' => '非法请求'
        );
    }
    echo json_encode($msg);
    die();
}
add_action( 'wp_ajax_nopriv_get_email_pass' , 'get_email_pass');

//找回密码 - 再次发送验证码
function again_send_vcode(){
    if( $_SESSION["Get_pwd_step"] == 2 && $_SESSION["Get_pwd_user"] ){
        $user = get_userdata($_SESSION["Get_pwd_user"]);
        if( send_vcode($user->data->user_email) ){
            $msg = array(
                'state' => 200,
                'tips' => '验证码发送成功',
            );
            $_SESSION["Get_pwd_step"] = 2;
        }else{
            $msg = array(
                'state' => 201,
                'tips' => '验证码发送失败，请稍候再试',
            );
        }
    }else{
        $msg = array(
            'state' => 404,
            'tips' => '非法请求'
        );
    }
    echo json_encode($msg);
    die();
}
add_action( 'wp_ajax_nopriv_again_send_vcode' , 'again_send_vcode' );

//验证码发送
function send_vcode($email){
    $code = rand(100000,999999);;
    $_SESSION['vcode'] = $code;
    $sendmail = '您好，您本次在'.get_bloginfo('name').'的验证码为：'.$code;
    $rel_mail = wp_mail( $email, get_bloginfo('name').'验证码邮件', $sendmail, 'Content-Type: text/html' );
    return $rel_mail;
}

//用户名、邮箱、手机账号中间字符串以*隐藏
function hideStar($str) {
  if (strpos($str, '@')) {
    $email_array = explode("@", $str);
    $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3); //邮箱前缀
    $count = 0;
    $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
    $rs = $prevfix . $str;
  } else {
    $pattern = '/(1[3458]{1}[0-9])[0-9]{4}([0-9]{4})/i';
    if (preg_match($pattern, $str)) {
      $rs = preg_replace($pattern, '$1****$2', $str); // substr_replace($name,'****',3,4);
    } else {
      $rs = substr($str, 0, 3) . "***" . substr($str, -1);
    }
  }
  return $rs;
}

