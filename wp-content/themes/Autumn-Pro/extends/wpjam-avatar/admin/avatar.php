<?php
function wpjam_edit_user_avatar_profile($profileuser){
	$avatarurl	= get_user_meta($profileuser->ID, 'avatarurl', true);

	echo '<h3>用户头像</h3>';

	wpjam_fields([
		'avatarurl'	=> ['title'=>'自定义头像', 'type'=>'img', 'item_type'=>'url', 'size'=>'200x200', 'value'=>$avatarurl]
	]); 
}
add_action('show_user_profile','wpjam_edit_user_avatar_profile',1);
add_action('edit_user_profile','wpjam_edit_user_avatar_profile',1);


function wpjam_edit_user_avatar_profile_update($user_id){

	if(current_user_can('edit_users') || get_current_user_id() == $user_id){

		$avatarurl	= $_POST['avatarurl'] ?: '';

		if($avatarurl){
			update_user_meta($user_id, 'avatarurl', $avatarurl);
		}else{
			delete_user_meta($user_id, 'avatarurl');
		}
	}
}
add_action('personal_options_update','wpjam_edit_user_avatar_profile_update');
add_action('edit_user_profile_update','wpjam_edit_user_avatar_profile_update');

add_action('admin_head', function(){
	?>
	<style type="text/css">
	.user-profile-picture{display: none;}
	</style>
	<?php
});



	



