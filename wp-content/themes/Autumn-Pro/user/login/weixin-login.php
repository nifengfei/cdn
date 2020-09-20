<?php 

$wpjam_qrcode	= wpjam_create_weixin_qrcode($key); 
if(is_wp_error($wpjam_qrcode)){
	print_R($wpjam_qrcode);
}

$scene		= $wpjam_qrcode['scene'];
$qrcode_url	= 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$wpjam_qrcode['ticket'];

?>
<div class="col-lg-7 col-md-12 col-pad-0 align-self-center">
	<div class="login-inner-form">
		<div class="details">
			<h3>微信扫码登录</h3>
			<div class="login-trps d-tips <?php if(isset($errors)){ echo 'error'; }?>">
				<?php if(isset($errors)){ echo $errors->get_error_message(); }?>
			</div>
			<form action="<?php echo home_url('user/weixin-login'); ?>" class="form login" method="POST" id="weixin_login_form">
				<div class="form-group weixin-img">
					<label for="code">
						<img src="<?php echo $qrcode_url; ?>" width="306">
						<input type="hidden" name="scene" value="<?php echo $scene; ?>">
					</label>
				</div>
				<div class="form-group code">
					<input id="code" type="text" name="code" class="input-text" value="" placeholder="输入验证码">
				</div>
				<div class="form-group">
					<input type="submit" name="submit" class="btn-md btn-theme" value="登录">
				</div>
			</form>
			<p>
				手机不在身旁？<a href="<?php echo home_url(user_trailingslashit('/user/login')); ?>"> 返回邮箱登录 </a>
			</p>
		</div>
	</div>
</div>