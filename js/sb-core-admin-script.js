(function($){

    var sb_option = $('div.sb-option'),
        option_form = $('#sb-options-form'),
        fileFrame = null,
        newPostID = 0,
        oldPostID = '',
        formField = '';

    // Disable current tab clicked
    (function(){
        $('div.sb-option .section-item > a').on('click', function(){
            var that = $(this);
            if(that.parent().hasClass('active')) {
                return false;
            }
        });
    })();

    // Hide save changes button for empty field
    (function(){
        if(!option_form.find('h3.setting-title').length && option_form.attr('data-page') != 'sb-options') {
            option_form.css({'display': 'none'});
        }
        if(option_form.find('div.sb-plugins').length) {
            option_form.find('p.submit').css({'display': 'none'});
            option_form.find('div.reset-button').css({'display': 'none'});
        }
    })();

    // Hide updated message
    (function(){
        setTimeout(function(){
            sb_option.find('div.updated').fadeOut(3000);
        }, 2000);
    })();

    // Turn on or turn off switch button
    (function(){
        sb_option.find('label.switch-button').each(function(){
            var that = $(this);
            that.click(function(){
                that = $(this);
                var dataSwitch = 'on',
                    switchValue = 0,
                    currentDataSwitch = that.attr('data-switch'),
                    otherButton;

                if(dataSwitch == currentDataSwitch) {
                    dataSwitch = 'off';
                    switchValue = 1;
                }

                otherButton = that.closest('div.switch-options').find('[data-switch="' + dataSwitch + '"]');
                otherButton.removeClass('active');
                that.addClass('active');
                that.closest('div.switch-options').find('input').val(switchValue);
            });
        });
    })();

    // Upload media button
    (function(){
        sb_option.find('a.sb-insert-media').each(function(){
            var that = $(this);
            that.click(function(event){

                that = $(this);
                event.preventDefault();

                formField = that.closest('div.sbtheme-upload').find('input');

                if(fileFrame) {
                    fileFrame.uploader.uploader.param( 'post_id', newPostID );
                    fileFrame.open();
                    return;
                }
                fileFrame = wp.media({title: 'Insert Media', button:{text: 'Use this image'}, multiple: false});
                fileFrame.on('select', function(){
                    sb_set_uploaded_image(fileFrame, formField);
                    formField = '';
                });
                fileFrame.open();
            });
        });
    })();

    function sb_set_uploaded_image(fileFrame, formField) {
        var mediaData = fileFrame.state().get('selection').first().toJSON();
        oldPostID = wp.media.model.settings.post.id;
        if(formField) {
            var imageSource = mediaData.url,
                mediaThumbnailBox = formField.closest('div.sbtheme-media-image').find('div.sbtheme.media.image');

            formField.val(imageSource);

            if(mediaThumbnailBox.length) {
                mediaThumbnailBox.addClass('uploaded');
                mediaThumbnailBox.html('<img src="' + imageSource + '">');
            }

        }
        wp.media.model.settings.post.id = oldPostID;
    }

    // Load SB Plugins
    (function(){
        var sb_plugins = sb_option.find('div.sb-plugins');
        if(sb_plugins.length) {
            var plugin_name = sb_plugins.attr('data-plugin'),
                plugins = plugin_name.split(',');

            $.each(plugins, function(index, value){
                var data = {
                    'action': 'sb_plugins',
                    'sb_plugin_slug': value
                };
                $.post(sb_core_admin_ajax.url, data, function(response){
                    sb_plugins.find('.sb-plugin-list').prepend(response);
                });
            });
            $(document).ajaxStop(function() {
                sb_plugins.find('.sb-ajax-load').fadeOut();
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

    // List sidebar option
    (function(){
        function append_new_sidebar(list) {
            var $li = $('<li class="ui-state-default sb-sidebar-custom"/>'),
                data_sidebar = parseInt(list.attr('data-sidebar')) + 1;
            $html = '<div class="sb-sidebar-line"><input type="text" name="' + list.attr('data-name') + '[' + data_sidebar + '][name]"><input type="text" name="' + list.attr('data-name') + '[' + data_sidebar + '][description]"><input type="text" name="' + list.attr('data-name') + '[' + data_sidebar + '][id]"></div>';
            list.attr('data-sidebar', data_sidebar);

            $html += '<img src="' + list.attr('data-icon-drag') + '" class="sb-icon-drag">';
            $html += '<img src="' + list.attr('data-icon-delete') + '" class="sb-icon-delete">';
            $li.attr('data-sidebar', data_sidebar);
            $li.html($html);
            list.append($li);
            list.parent().find('.sb-sidebar-count').val(data_sidebar);
            list.sortable('refresh');
        }
        if($('#sb-sortable-sidebar').length) {
            $('#sb-sortable-sidebar').sortable({
                cancel: ':input, .ui-state-disabled, .sb-icon-delete',
                placeholder: 'ui-state-highlight'
            });
        }
        $('button.sb-add-sidebar').on('click', function(e){
            e.preventDefault();
            var sortable_list = $(this).parent().find('.ui-sortable');
            append_new_sidebar(sortable_list);
            return false;
        });
        sb_option.delegate('.sb-icon-delete', 'click', function(){
            var that = $(this),
                $li = that.parent(),
                $list = $li.parent(),
                has_value = false,
                data_sidebar = parseInt($list.attr('data-sidebar')) - 1;
            if(!$li.hasClass('sb-default-sidebar')) {
                $li.find('input').each(function(){
                    if('' != $(this).val()) {
                        has_value = true;
                    }
                });
                if(has_value && confirm($list.attr('data-message-confirm'))) {
                    that.parent().remove();
                    $list.attr('data-sidebar', data_sidebar);
                    $list.parent().find('.sb-sidebar-count').val(data_sidebar);
                } else {
                    that.parent().remove();
                    $list.attr('data-sidebar', data_sidebar);
                    $list.parent().find('.sb-sidebar-count').val(data_sidebar);
                }
            }
        });
    })();

    (function(){
        $('span.deactivate > a').on('click', function(){
            var that = $(this),
                data = {
                    'action': 'sb_core_deactivate'
                },
                deactivate_link = that.attr('href');

            if(deactivate_link.indexOf('sb-core') != -1) {
                $.post(sb_core_admin_ajax.url, data, function(response){
                    if(confirm(response)){
                        window.location.href = deactivate_link;
                    } else {
                        return false;
                    }
                });
                return false;
            }

        });
    })();

})(jQuery);