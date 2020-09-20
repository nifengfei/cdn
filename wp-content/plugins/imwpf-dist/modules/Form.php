<?php
 namespace imwpf\modules; class Form { protected $form = array(); protected $formID; protected $input = array(); public function __construct($method = 'POST', $action = '') { $this->form = array( 'method' => $method, 'action' => $action, 'data' => &$this->input ); $this->formID = $this->createId(); wp_enqueue_script('ajaxupload', IMWPF_URL . 'views/ajaxupload.js', array('jquery')); } public static function createId() { $i = 0; $chr = ''; while ($i < 5) { $chr .= chr(mt_rand(97, 122)); $i++; } return $chr; } public function createScript() { ?>
        <style type="text/css">
            #<?php echo $this->formID; ?>.add,
            #<?php echo $this->formID; ?>.minus {
                cursor: pointer;
                display: inline-block;
                width: 20px;
                height: 20px;
                line-height: 20px;
                border: 1px solid #ccc;
                border-radius: 3px;
                text-align: center;
                margin-top: 0px;
                background: #fff;
            }

            p.description {
                color: #999;
            }

            .upload_button_div .button {
                margin-right: 10px;
            }

            .uploaded-image {
                max-width: 340px;
                height: auto;
                margin-top: 10px;
            }

            .clearfix:after {
                content: "\20";
                display:block;
                height:0;
                clear: both;
            }

            .imwpf-form-container {
                margin: 15px;
                margin-left:0px;
                width: 785px;
                position:relative;
                z-index: 0;
                background-color: #e8e8e8;
            }

            .imwpf-form-container .content {
                float: left;
                min-height: 550px;
                width: 595px;
                margin-left: -1px;
                padding: 0 14px;
                font-family: "Lucida Grande", Sans-serif;
                background-color: #fff;
                border-left: 1px solid #d8d8d8;
            }

            .imwpf-form-container .content-long {
                width: 785px;
                border-left:none;
            }

            .imwpf-form-container .content h2 {
                padding-bottom:10px;
                border-bottom:1px solid #ccc;
            }

            .imwpf-form-nav {
                float: left;
                position: relative;
                z-index: 9999;
                width: 160px;
            }
            .imwpf-form-nav li {
                margin-bottom:0;
            }
            .imwpf-form-nav ul li a:link, .imwpf-form-nav ul li a:visited {
                display: block;
                padding: 10px 10px 10px 15px;
                font-family: Georgia, Serif;
                font-size: 13px;
                text-decoration: none;
                color: #797979;
                border-bottom: 1px solid #d8d8d8;
            }
            .imwpf-form-nav ul li.current a, .imwpf-form-nav ul li a:hover {
                color: #21759b;
                background-color: #fff;
            }
        </style>

        <script type="text/javascript">
            //加号按钮效
            jQuery(document).ready(function($) {

                $('#<?php echo $this->formID; ?>').delegate('.add', 'click', function() {
                    var input = $(this).parent().html();
                    input = input.replace('<span class="add">+</span>', '');
                    newinput = '<div>' + input + '<span class="minus">-</span></div>';
                    //追加新的标签
                    $(this).parent().parent().append(newinput);
                });

                //减号按钮效果
                var input = {};
                $('#<?php echo $this->formID; ?>').delegate('.minus', 'click', function() {
                    $(this).parent().remove();
                });


                // ajax 上传
                $('.imwpf_image_upload_button').each(function() {
                    var clickedObject = $(this);
                    var clickedID = $(this).attr('id');
                    var nonce = $('#security').val();

                    new AjaxUpload(clickedID, {
                        action: ajaxurl,
                        name: clickedID, // File upload name
                        data: {
                            action: 'imwpf_upload',
                            type: 'upload',
                            security: nonce,
                            data: clickedID
                        },
                        autoSubmit: true, // Submit file after selection
                        responseType: false,
                        onChange: function(file, extension) {},
                        onSubmit: function(file, extension) {
                            clickedObject.text('上传中.'); // change button text, when user selects file   
                            this.disable(); // If you want to allow uploading only 1 file at time, you can disable upload button
                            interval = window.setInterval(function() {
                                var text = clickedObject.text();
                                if (text.length < 13) {
                                    clickedObject.text(text + '.');
                                } else {
                                    clickedObject.text('上传中.');
                                }
                            }, 200);
                        },
                        onComplete: function(file, response) {
                            response = JSON.parse(response)
                            window.clearInterval(interval);
                            clickedObject.text('上传图片');
                            this.enable(); // enable upload button
                            // If nonce fails
                            if (response.errno != 0) {
                                alert("上传失败，原因" + response.msg)
                            } else {
                                var buildReturn = '<img class="uploaded-image" id="image_' + clickedID + '" src="' + response.data + '" alt="" />';
                                $(".upload-error").remove();
                                $("#image_" + clickedID).remove();
                                clickedObject.parent().after(buildReturn);
                                $('img#image_' + clickedID).fadeIn();
                                clickedObject.next('span').fadeIn();
                                clickedObject.parent().prev('input').val(response.data);
                            }
                        }
                    });

                });

                // 移除图片
                $('.imwpf_image_reset_button').click(function() {
                    var theID = $(this).attr('title');
                    var image_to_remove = $('#image_' + theID);
                    var button_to_hide = $('#reset_' + theID);
                    image_to_remove.fadeOut(500, function() {
                        $(this).remove();
                    });
                    button_to_hide.fadeOut();
                    $(this).parent().prev('input').val('');
                });

                // tab
                $('.group').hide();
                $('.group:first').fadeIn();
                $('.imwpf-form-nav li:first').addClass('current');
                $('.imwpf-form-nav li a').click(function(evt) { 
                    $('.imwpf-form-nav li').removeClass('current');
                    $(this).parent().addClass('current');
                    var clicked_group = $(this).attr('href');
                    $('.group').hide();             
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });

            });
        </script>
<?php
 } public function text($name, $value = null, $label = null, $desc = null, $is_hide = false) { if (!$label) { $label = $name; } $this->input[] = array( 'type' => 'text', 'name' => $name, 'value' => $value, 'label' => $label, 'desc' => $desc, 'is_hide' => $is_hide, ); return $this; } public function textarea($name, $value = null, $label = null, $desc = null, $is_hide = false) { if (!$label) { $label = $name; } $this->input[] = array( 'type' => 'textarea', 'name' => $name, 'value' => $value, 'label' => $label, 'desc' => $desc, 'is_hide' => $is_hide, ); return $this; } public function multiCheck($name, $options, $values, $label = null, $desc = null) { if (!$label) { $label = $name; } if (empty($values) || !is_array($values)) { $values = array(); } $this->input[] = array( 'type' => 'multicheck', 'name' => $name, 'options' => $options, 'value' => $values, 'label' => $label, 'desc' => $desc ); return $this; } public function select($name, $options, $value, $label = null, $desc = null) { if (!$label) { $label = $name; } $this->input[] = array( 'type' => 'select', 'name' => $name, 'options' => $options, 'value' => $value, 'label' => $label, 'desc' => $desc ); return $this; } public function label($name, $value = null, $label = null) { if (!$label) { $label = $name; } $this->input[] = array( 'type' => 'label', 'name' => $name, 'value' => $value, 'label' => $label ); return $this; } public function tab($name, $value) { $this->input[] = array( 'type' => 'tab', 'name' => $name, 'value' => $value, ); return $this; } public function hidden($name, $value) { $this->input[] = array( 'type' => 'hidden', 'name' => $name, 'value' => $value, ); return $this; } public function image($name, $value = null, $label = null) { if (!$label) { $label = $name; } $this->input[] = array( 'type' => 'image', 'name' => $name, 'value' => $value, 'label' => $label ); return $this; } public function render() { return $this->get($this->form); } public function get($data) { $this->createScript(); $submitText = isset($data['submit']) ? $data['submit'] : '提交'; $form = "<form method='{$data['method']}' id='{$this->formID}' action='{$data['action']}' enctype='multipart/form-data' novalidate='novalidate'>
        <div class='form-table'>"; $counter = 0; $menu = ""; $hasTab = false; foreach ($data['data'] as $row) { $counter++; switch ($row['type']) { case 'text': $form .= self::getText($row); break; case 'textarea': isset($row['desc']) ? $row['desc'] = "<p class=\"description\">{$row['desc']}</p>" : $row['desc'] == ''; $row['value'] = !empty($row['value']) ? $row['value'] : ''; $row['desc'] = !empty($row['desc']) ? $row['desc'] : ''; $form .= "<div><h4>{$row['label']}</h4>"; $form .= "<td><textarea class=\"regular-text\" name=\"{$row['name']}\" rows=5>{$row['value']}</textarea>{$row['desc']}</td></div>"; break; case 'select': isset($row['desc']) ? $row['desc'] = "<p class=\"description\">{$row['desc']}</p>" : $row['desc'] == ''; $form .= "<div><h4>{$row['label']}</h4>"; $form .= "<td scope=\"row\"><select name=\"{$row['name']}\">"; foreach ($row['options'] as $k => $v) { if ($row['value'] == $k) { $form .= "<option id=\"{$row['name']}-{$k}\" value=\"{$k}\" selected=\"selected\"><label for=\"{$row['name']}-{$k}\">{$v}</option>"; } else { $form .= "<option id=\"{$row['name']}-{$k}\" value=\"{$k}\"><label for=\"{$row['name']}-{$k}\">{$v}</label></option>"; } } $form .= "</select>{$row['desc']}</td></div>"; break; case 'multicheck': isset($row['desc']) ? $row['desc'] = "<p class=\"description\">{$row['desc']}</p>" : $row['desc'] == ''; $form .= "<div><h4>{$row['label']}</h4>"; $form .= "<td scope=\"row\">"; foreach ($row['options'] as $k => $v) { if (in_array($k, $row['value'])) { $form .= "<p><input id=\"{$row['name']}-{$k}\" name=\"{$row['name']}[]\" type=\"checkbox\" checked=\"checked\" value=\"{$k}\"><label for=\"{$row['name']}-{$k}\">{$v}</label></p>"; } else { $form .= "<p><input id=\"{$row['name']}-{$k}\" name=\"{$row['name']}[]\" type=\"checkbox\" value=\"{$k}\"><label for=\"{$row['name']}-{$k}\">{$v}</label></p>"; } } $form .= "{$row['desc']}</td></div>"; break; case 'check': isset($row['desc']) ? $row['desc'] = "<p class=\"description\">{$row['desc']}</p>" : $row['desc'] == ''; $form .= "<div><h4>{$row['label']}</h4>"; $form .= "<td scope=\"row\">"; if ($row['value'] == $row['option']) { $extra = 'checked = "checked"'; } else { $extra = ''; } $form .= "<p>
                        <input id=\"{$row['name']}\" name=\"{$row['name']}\" type=\"checkbox\" {$extra} value=\"{$row['option']}\">
                        <label for=\"{$row['name']}\">{$row['label']}</label>
                    </p>"; $form .= "{$row['desc']}</td></div>"; break; case 'image': $form .= "<div><h4>{$row['label']}</h4>"; isset($row['desc']) ? $row['desc'] = "<p class=\"description\">{$row['desc']}</p>" : $row['desc'] == ''; $form .= '<td>' . $this->uploader($row['name'], $row['value']) . '</td></div>'; break; case 'hidden': $form .= "<input name=\"{$row['name']}\" type=\"hidden\" value=\"{$row['value']}\">"; break; case 'label': $form .= '<div><h4>' . $row['name'] . '</h4><td>' . $row['value'] . '</td></div>'; break; case 'tab': $hasTab = true; if($counter >= 2) { $form .= '</div>'."\n"; } $clickID = preg_replace("/[^A-Za-z0-9]/", "", strtolower($row['name']) ); $clickID = "imwpf-click-" . $clickID; $menu .= '<li><a title="'. $row['value'] .'" href="#'. $clickID .'">'. $row['value'] .'</a></li>'; $form .= '<div class="group" id="'. $clickID .'"><h2>'.$row['value'].'</h2>'."\n"; break; } $form .= "</tr>"; } $form .= '<input type="hidden" id="security" name="security" value="' . wp_create_nonce('imwpf_upload_nonce') . '" />'; $submit = "<p class='submit'><input type='submit' name='submit' id='submit' class='button button-primary' value='{$submitText}'></p>"; $form .= '</div>' . $submit; if ($hasTab) { $form .= "</div>"; } $form .= '</form>'; if (!empty($menu)) { return '<div class="imwpf-form-container clearfix">
                <div class="imwpf-form-nav"><ul>' . $menu . '</ul></div><div class="content">' . $form . '</div></div>'; } return '<div class="imwpf-form-container clearfix">
                    <div class="content content-long">' . $form . '</div>
                </div>'; } public function upload($name) { if (!isset($_FILES[$name]['name'])) { return false; } $file = $_FILES[$name]; $override['test_form'] = false; $override['action'] = 'wp_handle_upload'; $uploadedFile = wp_handle_upload($file, $override); if (isset($uploadedFile['url'])) { $wpUploadDir = wp_upload_dir(); $attachment = array( 'guid' => $wpUploadDir['url'] . '/' . basename($uploadedFile['file']), 'post_mime_type' => $uploadedFile['type'], 'post_title' => preg_replace('/\.[^.]+$/', '', basename($uploadedFile['file'])), 'post_content' => '', 'post_status' => 'inherit' ); $attachId = wp_insert_attachment($attachment, $uploadedFile['file']); require_once(ABSPATH . 'wp-admin/includes/image.php'); $attachData = wp_generate_attachment_metadata($attachId, $uploadedFile['file']); wp_update_attachment_metadata($attachId, $attachData); return $uploadedFile['url']; } else { return false; } } protected static function getText($input) { $name = $input['name']; $input['value'] = !empty($input['value']) ? $input['value'] : ''; $input['desc'] = !empty($input['desc']) ? $input['desc'] : ''; if (isset($input['is_hide']) && $input['is_hide'] == true) { $trClass = "hidden"; } else { $trClass = "show"; } if (is_array($input['value'])) { $row = "<div class=\"$trClass\" id=\"input-$name\"><h4><label>{$input['label']}</label></h4>"; foreach ($input['value'] as $k => $v) { if ($k == 0) { $adi = '<span class="add">+</span>'; } else { $adi = '<span class="minus">-</span>'; } $row .= " <div>
                        <input class=\"regular-text\" type=\"text\" name=\"{$name}\" value=\"{$v}\">
                        {$adi}
                    </div>"; } $row .= "<p class=\"description\">{$input['desc']}</p></div>"; } else { $adi = isset($input['add']) ? '<span class="add">+</span>' : ''; $row = "<div class=\"$trClass\" id=\"input-$name\">
                    <h4><label>{$input['label']}</label></h4>
                    <div>
                        <input class=\"regular-text\" type=\"text\" name=\"{$name}\" value=\"{$input['value']}\">
                        {$adi}
                    </div>
                    <p class=\"description\">{$input['desc']}</p>
                    </div>"; } if (!empty($input['show'])) { $key = $input['show']['key']; $value = json_encode($input['show']['value']); $script = <<<EOF
<script>
jQuery("input[name='{$key}'],select[name='{$key}']").change(function(){
    var arr = $value
    value = jQuery(this).val();
    if (arr.indexOf(value) != -1) {
        jQuery("#input-{$name}").show();
    } else {
        jQuery("#input-{$name}").hide();
    }
});
jQuery(document).ready(function(){
    var arr = $value;
    value = jQuery("input[name='{$key}'], select[name='{$key}']").val();
    if (arr.indexOf(value) != -1) {
        jQuery("#input-{$name}").show();
    } else {
        jQuery("#input-{$name}").hide();
    }
});

</script>
EOF;
} return $row . $script; } public static function uploader($id, $upload) { $uploader = '<input name="' . $id . '" id="' . $id . '_upload" type="hidden" value="' . $upload . '" />'; $uploader .= '<div class="upload_button_div"><span class="button imwpf_image_upload_button" id="' . $id . '">上传图片</span>'; if (!empty($upload)) { $hide = ''; } else { $hide = 'hide'; } $uploader .= '<span class="button imwpf_image_reset_button ' . $hide . '" id="reset_' . $id . '" title="' . $id . '">移除</span>'; $uploader .= '<div class="clear"></div>'; if (!empty($upload)) { $uploader .= '<a href="' . $upload . '">'; $uploader .= '<img class="uploaded-image" id="image_' . $id . '" src="' . $upload . '" alt="" />'; $uploader .= '</a>'; } return $uploader; } } 