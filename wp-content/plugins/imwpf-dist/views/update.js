jQuery(document).ready(function($){

    function imwp_show(caption, content) {
    var $closeBtn;
    try {
        if (typeof document.body.style.maxHeight === "undefined") {//if IE 6
            jQuery("body","html").css({height: "100%", width: "100%"});
            jQuery("html").css("overflow","hidden");
            if (document.getElementById("TB_HideSelect") === null) {//iframe to hide select elements in ie6
                jQuery("body").append("<iframe id='TB_HideSelect'>"+thickboxL10n.noiframes+"</iframe><div id='TB_overlay'></div><div id='TB_window' class='thickbox-loading'></div>");
                jQuery("#TB_overlay").click(tb_remove);
            }
        }else{//all others
            if(document.getElementById("TB_overlay") === null){
                jQuery("body").append("<div id='TB_overlay'></div><div id='TB_window' class='thickbox-loading'></div>");
                jQuery("#TB_overlay").click(tb_remove);
                jQuery( 'body' ).addClass( 'modal-open' );
            }
        }

        if(tb_detectMacXFF()){
            jQuery("#TB_overlay").addClass("TB_overlayMacFFBGHack");//use png overlay so hide flash
        }else{
            jQuery("#TB_overlay").addClass("TB_overlayBG");//use background and opacity
        }

        if (caption===null) {
            caption="";
        }
        jQuery("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");//add loader to the page
        jQuery('#TB_load').show(); //show loader


        TB_WIDTH = 630; //defaults to 630 if no parameters were added to URL
        TB_HEIGHT = 440; //defaults to 440 if no parameters were added to URL
        ajaxContentW = TB_WIDTH - 30;
        ajaxContentH = TB_HEIGHT - 45;

        if(jQuery("#TB_window").css("visibility") != "visible"){
            jQuery("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+caption+"</div><div id='TB_closeAjaxWindow'><button type='button' id='TB_closeWindowButton'><span class='screen-reader-text'>"+thickboxL10n.close+"</span><span class='tb-close-icon'></span></button></div></div><div id='TB_ajaxContent' style='width:"+ajaxContentW+"px;height:"+ajaxContentH+"px'></div>");
            jQuery("#TB_overlay").unbind();
        } else {
            jQuery("#TB_ajaxContent")[0].style.width = ajaxContentW +"px";
            jQuery("#TB_ajaxContent")[0].style.height = ajaxContentH +"px";
            jQuery("#TB_ajaxContent")[0].scrollTop = 0;
            jQuery("#TB_ajaxWindowTitle").html(caption);
        }

        jQuery("#TB_closeWindowButton").click(tb_remove);

        if (!content) {
            jQuery("#TB_ajaxContent").html("<div style='margin:100px auto; width:208px;'><img src='"+imgLoader.src+"' width='208' /></div>");//add loader to the page
        } else {
            jQuery("#TB_ajaxContent").html(content);
        }
        tb_position();
        jQuery("#TB_load").remove();
        tb_init("#TB_ajaxContent a.thickbox");
        jQuery("#TB_window").css({'visibility':'visible'});

        $closeBtn = jQuery( '#TB_closeWindowButton' );
        /*
         * If the native Close button icon is visible, move focus on the button
         * (e.g. in the Network Admin Themes screen).
         * In other admin screens is hidden and replaced by a different icon.
         */
        if ($closeBtn.find( '.tb-close-icon' ).is( ':visible' )) {
            $closeBtn.focus();
        }

    } catch(e) {
        //nothing here
        //console.log(e)
    }
}
    
    $(".imwp-update").click(function() {
        imwp_show("更新中,可能需要较长时间...", "")
        var name = $(this).attr('data-name');
        var type = $(this).attr('data-type');
        if (!name || !type) {
            $("#TB_ajaxContent").html("数据不正确...");
            return false;
        }
        var self = $(this);
        $.ajax({
            url: ajaxurl + '?action=imwpf_updater',
            method: 'POST',
            dataType: 'json',
            data: {
                "name": name,
                "cert": "",
                "type": type,
                "sub_action": "do_update"
            },
            success: function(data) {
                var content = '';
                if (data.errno == 0) {
                    if (data.data.length == 0) {
                        content += "<p>升级成功，请刷新页面!</p>";
                    } else {
                        for (var i in data.data) {
                            content += '<p>替换文件失败:' + data.data[i] + '，请将主题目录的权限设为可写再重新更新</p>';
                        }
                    }
                } else {
                    content += '<p>' + data.msg + '</p>';
                }
                $("#TB_ajaxContent").html(content);
            },
            error: function() {
                $("#TB_ajaxContent").html('<p>请求更新失败</p>');
            }
        });
    });

    $("#check_update").click(function(){
        $("#check_result").html("<p>检查更新中，请稍候...</p>");
        $.ajax({
            url: ajaxurl + '?action=imwpf_updater',
            method: 'POST',
            dataType: 'json',
            data: {
                "name": imwpfUpdateName,
                "cert": "",
                "type": imwpfUpdateType,
                "sub_action": "check"
            },
            success: function(data) {
                if (data.msg == '有新的版本') {
                    var content = "<p>" +  data.msg + " 可以 <button class=\"button button-primary\" id=\"do_update\">立即升级</button></p>";
                } else {
                    var content = "<p>" + data.msg + " 可以 <button class=\"button button-primary\" id=\"do_update\">重新安装</button></p>";
                }
                $("#check_result").html(content);
            },
            error: function() {
                $("#check_result").html("<p>请求更新失败</p>");
            }
        });
    });

    $("#check_result").delegate("#do_update", "click", function(){
        $("#check_result").html("<p>更新中，请稍候...</p>");
        $.ajax({
            url: ajaxurl + '?action=imwpf_updater',
            method: 'POST',
            dataType: 'json',
            data: {
                "name": imwpfUpdateName,
                "cert": "",
                "type": imwpfUpdateType,
                "sub_action": "do_update"
            },
            success: function(data) {
                var content = '';
                if (data.errno == 0) {
                    if (data.data.length == 0) {
                        content += "<p>升级成功</p>";
                    } else {
                        for (var i in data.data) {
                            content += '<p>替换文件失败:' + data.data[i] + '</p>';
                        }
                    }
                } else {
                    content += data.msg;
                }
                $("#check_result").html(content);
                //console.log(data);
            },
            error: function() {
                $("#check_result").html("<p>请求更新失败</p>");
            }
        });
    });
});
