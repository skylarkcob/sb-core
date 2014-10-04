(function($){
    var sb_option = $("div.sb-option"),
        option_form = $("#sb-options-form"),
        fileFrame = null,
        newPostID = 0,
        oldPostID = wp.media.model.settings.post.id;

    // Disable current tab clicked
    (function(){
        $("div.sb-option .section-item > a").on("click", function(){
            var that = $(this);
            if(that.parent().hasClass("active")) {
                return false;
            }
        });
    })();

    // Hide save changes button for empty field
    (function(){
        if(!option_form.find("h3.setting-title").length && option_form.attr("data-page") != "sb-options") {
            option_form.css({"display": "none"});
        }
        if(option_form.find('div.sb-plugins').length) {
            option_form.find('p.submit').css({"display": "none"});
            option_form.find('div.reset-button').css({'display': 'none'});
        }
    })();

    // Hide updated message
    (function(){
        setTimeout(function(){
            sb_option.find("div.updated").fadeOut(3000);
        }, 2000);
    })();

    // Turn on or turn off switch button
    (function(){
        sb_option.find("label.switch-button").each(function(){
            var that = $(this);
            that.click(function(){
                that = $(this);
                var dataSwitch = "on",
                    switchValue = 0,
                    currentDataSwitch = that.attr('data-switch'),
                    otherButton;

                if(dataSwitch == currentDataSwitch) {
                    dataSwitch = 'off';
                    switchValue = 1;
                }

                otherButton = that.closest('div.switch-options').find("[data-switch='" + dataSwitch + "']");
                otherButton.removeClass("active");
                that.addClass("active");
                that.closest('div.switch-options').find("input").val(switchValue);
            });
        });
    })();

    // Upload media button
    (function(){
        sb_option.find("a.sb-insert-media").each(function(){
            var that = $(this);
            that.click(function(event){

                that = $(this);
                event.preventDefault();

                formField = that.closest("div.sbtheme-upload").find("input");

                if(fileFrame) {
                    fileFrame.uploader.uploader.param( 'post_id', newPostID );
                    fileFrame.open();
                    return;
                }
                fileFrame = wp.media({title: 'Insert Media', button:{text: 'Use this image'}, multiple: false});
                fileFrame.on("select", function(){
                    sb_set_uploaded_image(fileFrame, formField);
                    formField = '';
                });
                fileFrame.open();
            });
        });
    })();

    function sb_set_uploaded_image(fileFrame, formField) {
        var mediaData = fileFrame.state().get("selection").first().toJSON();
        if(formField) {
            var imageSource = mediaData.url,
                mediaThumbnailBox = formField.closest("div.sbtheme-media-image").find("div.sbtheme.media.image");

            formField.val(imageSource);

            if(mediaThumbnailBox.length) {
                mediaThumbnailBox.addClass("uploaded");
                mediaThumbnailBox.html('<img src="' + imageSource + '">');
            }

        }
        wp.media.model.settings.post.id = oldPostID;
    }

    // Load SB Plugins
    (function(){
        if(sb_option.find('div.sb-plugins').length) {
            var data = {
                'action': 'sb_plugins'
            };
            $.post(sb_core_admin_ajax.url, data, function(response){
                sb_option.find('div.sb-plugins > div').html(response);
            });
        }
    })();

    (function(){
        sb_option.find('div.reset-button > span').on('click', function(){
            var that = $(this),
                data_page = that.parent().parent().attr('data-page'),
                data = {
                    'action': 'sb_option_reset',
                    'sb_option_page': that.parent().parent().attr('data-page')
                };

            that.find('img').css({'display': 'inline-block'});
            $.post(sb_core_admin_ajax.url, data, function(response){
                var data_option = $.parseJSON(response),
                    option_form = that.parent().parent();
                if(typeof data_option == 'object') {
                    if(data_page == 'sb_paginate') {
                        option_form.find('input[name="sb_options[paginate][label]"]').val(data_option['label']);
                        option_form.find('input[name="sb_options[paginate][next_text]"]').val(data_option['next_text']);
                        option_form.find('input[name="sb_options[paginate][previous_text]"]').val(data_option['previous_text']);
                        option_form.find('input[name="sb_options[paginate][range]"]').val(data_option['range']);
                        option_form.find('input[name="sb_options[paginate][anchor]"]').val(data_option['anchor']);
                        option_form.find('input[name="sb_options[paginate][gap]"]').val(data_option['gap']);
                        option_form.find('select[name="sb_options[paginate][style]"]').val(data_option['style']);
                        option_form.find('select[name="sb_options[paginate][border_radius]"]').val(data_option['border_radius']);
                    }
                }
                that.find('img').css({'display': 'none'});
            });
        });
    })();

})(jQuery);