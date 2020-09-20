<?php
$wpjam_qrcode	= wpjam_create_weixin_qrcode($key); 
if(is_wp_error($wpjam_qrcode)){
	print_R($wpjam_qrcode);
}

$scene		= $wpjam_qrcode['scene'];
$qrcode_url	= 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$wpjam_qrcode['ticket'];
?>
<div class="wp-editor col-lg-9">
	<div class="row posts-wrapper2">
		<div class="tougao weixin-bind">
			<h3 class="section-title"><span>绑定微信账号</span></h3>
				<div class="login-inner-form">
					<div class="details">
					<?php
					$user_id	= get_current_user_id();

					$openid			= wpjam_get_user_weixin_openid($user_id, 'weixin');
					$weixin_user	= []; 
						
					if($openid){
						$weixin_user	= wpjam_get_weixin_user($openid);

						if(!$weixin_user){
							$errors	= new WP_Error('invalid_bind', '绑定错误，请重新改绑定！');
						}
					} ?>

					<?php if($weixin_user){ ?>

						<p>您已经成功绑定微信账号，信息如下：</p>

						<table class="user-weixin-bind">
						<thead>
						<tr>
							<th>
								微信昵称
							</th>
							<th>
								头像
							</th>
							<th>
								地区
							</th>
							<th>
								操作
							</th>
						</tr>
						</thead>
						<tbody>
						<tr class="relative">
							<td>
								<?php echo $weixin_user['nickname']; ?>
							</td>
							<td>
								<img src="<?php echo str_replace('/132', '/0', $weixin_user['headimgurl']); ?>" alt="<?php echo $weixin_user['nickname']; ?>">
							</td>
							<td>
								<?php echo $weixin_user['province'].' '.$weixin_user['city']; ?>
							</td>
							<td>
								<a class="weixin-unbind" href="<?php echo home_url('user/weixin-bind?unbind'); ?>">解除绑定</a>
							</td>
						</tr>
						</tbody>
						</table>

					<?php }else{ ?>

					<p>使用微信扫描二维码，将获取到的验证码填写到下面进行绑定</p>
					<div class="login-trps d-tips <?php if(isset($errors)){ echo 'error'; }?>">
						<?php if(isset($errors)){ echo $errors->get_error_message(); }?>
					</div>
					<form action="<?php echo home_url('user/weixin-bind'); ?>" class="form login" method="POST" id="weixin_login_form">
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
							<input type="submit" name="submit" class="btn-md btn-theme" value="立即绑定">
						</div>
					</form>

					<?php } ?>
						
				</div>
			</div>
		</div>
	</div>
</div>