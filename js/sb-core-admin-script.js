(function($){

    var sb_option = $('div.sb-option'),
        option_form = $('#sb-options-form'),
        file_frame = null,
        new_post_id = 0,
        old_post_id = '',
        body = $('body');

    function sb_is_image_url(url) {
        var result = true,
            extension = url.slice(-4);
        $('<img>', {
            src: url,
            error: function() { result = false; },
            load: function() { result = true; }
        });
        if(extension != '.png' && extension != '.jpg' && extension != '.gif' && extension != '.bmp'  && extension != 'jpeg') {
            result = false;
        }
        return result;
    }

    function sb_is_url(text) {
        var url_regex = new RegExp('^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}([0-9A-Za-z]+\.)');
        if(url_regex.test(text)) {
            return true;
        }
        return false;
    }

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
            sb_option.find('div.updated').fadeOut(2000);
            var page_url = window.location.href;
            if(page_url.indexOf('settings-updated') >= 0 && sb_option.length) {
                page_url = page_url.slice(0, page_url.indexOf('&'));
                window.history.pushState('string', '', page_url);
            }
        }, 1000);
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

    // Deactivate SB Core
    (function(){
        $('#sb-core span.deactivate > a').on('click', function(){
            var that = $(this),
                data = {
                    'action': 'sb_core_deactivate_message'
                },
                deactivate_link = that.attr('href');

            if(deactivate_link.indexOf('sb-core') != -1) {
                $.post(sb_core_admin_ajax.url, data, function(response){
                    if(confirm(response)){
                        data = {
                            action: 'sb_deactivate_all_sb_product'
                        };
                        $.post(sb_core_admin_ajax.url, data, function(){
                            window.location.href = deactivate_link;
                        });
                    } else {
                        return false;
                    }
                });
                return false;
            }

        });
        if($('#sb-core').hasClass('inactive')) {
            var data = {
                action: 'sb_deactivate_all_sb_product'
            };
            $.post(sb_core_admin_ajax.url, data, function(response){

            });
        }
    })();

    // UI Sortable List
    (function(){
        $('ul.sb-sortable-list.active-sortable').each(function(i, el){
            var that = $(this),
                sortable_active_list = that,
                sortable_container = sortable_active_list.closest('div.sb-sortable'),
                sortable_source_list = null,
                sortable_source_list_height = 0,
                sortable_active_list_height = 0;
            if(sortable_active_list.length) {
                sortable_source_list = sortable_container.find('ul.sb-sortable-list.sortable-source')
                if(sortable_source_list.length) {
                    sortable_active_list_height = sortable_active_list.height();
                    sortable_source_list_height = sortable_source_list.height();
                    if(sortable_active_list_height < sortable_source_list_height) {
                        sortable_active_list.css({'height': sortable_source_list_height});
                    }
                }
            }
        });
        if(!$('ul.sb-sortable-list').hasClass('ui-sortable')) {
            var remove_item = false,
                sortable_container = null;
            if(!$('ul.sb-sortable-list').length) {
                return;
            }
            $('ul.sb-sortable-list').sortable({
                cancel: ':input, .ui-state-disabled, .sb-icon-delete',
                connectWith: '.connected-sortable',
                placeholder: 'ui-state-highlight',
                receive: function(event, ui) {
                    remove_item = false;
                },
                over: function(event, ui) {
                    remove_item = false;
                },
                out: function(event, ui) {
                    remove_item = true;
                },
                beforeStop: function(event, ui) {
                    var that = $(ui.item),
                        sortable_list = that.parent();
                    if(remove_item && sortable_list.hasClass('out-remove')) {
                        var ui_panel = ui.item.closest('div.sb-ui-panel'),
                            input_count = ui_panel.find('input.ui-item-count'),
                            count = parseInt(input_count.val());
                        count--;
                        input_count.val(count);
                        ui_panel.find('button.ui-add-item').attr('data-count', count);
                        ui.item.remove();
                    }
                },
                sort: function(event, ui) {
                    var that = $(this);
                    that.find('.ui-state-highlight').css({'height': ui.item.height()});
                },
                stop: function(event, ui) {
                    var data = '',
                        that = $(ui.item),
                        single_ui_order = null,
                        sortable_connect_active = null,
                        sortable_container = that.closest('div.sb-sortable'),
                        sortable_active_list = sortable_container.find('.sb-sortable-list.active-sortable'),
                        sortable_source_list = sortable_container.find('.sb-sortable-list.sortable-source');

                    if(sortable_active_list.children().length < sortable_source_list.children().length) {
                        sortable_active_list.css({'height': sortable_source_list.height()});
                    } else {
                        sortable_active_list.css({'height': 'auto'});
                    }
                    single_ui_order = sortable_container.find('input.ui-item-order');
                    sortable_connect_active = sortable_container.find('input.active-sortalbe-value');
                    if(single_ui_order.length) {
                        sortable_container.find('ul.sb-sortable-list li').each(function(i, el){
                            var p = $(el).find('.ui-item-id').val();
                            data += p + ',';
                        });
                        data = data.slice(0, -1);
                        single_ui_order.val(data);
                    }
                    if(sortable_connect_active.length) {
                        data = '';
                        sortable_container.find('ul.sb-sortable-list.active-sortable li').each(function(i, el){
                            var p = $(el).attr('data-term');
                            if(data.indexOf(p) != -1) {
                                return;
                            } else {
                                data += p + ',';
                            }
                        });
                        data = data.slice(0, -1);
                        sortable_container.find('.active-sortalbe-value').val(data);
                    }
                }
            }).disableSelection();
        }
    })();

    function sb_build_add_ui_data(button) {
        var that = button,
            data_name = that.attr('data-name'),
            data_count = that.attr('data-count'),
            data_type = that.attr('data-type'),
            ui_panel = that.closest('div.sb-ui-panel'),
            input_order = ui_panel.find('.ui-item-order'),
            next_id = button.attr('data-next-id'),
            order = input_order.val(),
            data = null;
        data = {
            action: 'sb_add_ui_item',
            data_name: data_name,
            data_count: data_count,
            data_type: data_type,
            data_id: next_id
        };
        data_count++;
        if(!order.trim()) {
            order += next_id;
        } else {
            order += ',' + next_id;
        }

        next_id++;
        button.attr('data-next-id', next_id);
        ui_panel.find('input.ui-item-count').val(data_count);
        input_order.val(order);
        button.attr('data-count', data_count);
        return data;
    }

    function sb_switch_reset_ajax(button, show) {
        if(show) {
            button.find('img').addClass('active');
        } else {
            button.find('img').removeClass('active');
        }
    }

    function sb_reset_ui_complete(button) {
        var ui_panel = button.closest('div.sb-ui-panel'),
            add_button = ui_panel.find('button.ui-add-item'),
            input_order = ui_panel.find('input.ui-item-order'),
            input_count = ui_panel.find('input.ui-item-count');
        button.closest('div').find('.sb-sortable-list').html('');
        input_order.val('');
        input_count.val(0);
        add_button.attr('data-count', 0);
        add_button.attr('data-next-id', 1);
        sb_switch_reset_ajax(button, false);
    }

    (function(){
        $('.sb-ui-panel button.reset').on('click', function(e){
            e.preventDefault();
            var that = $(this),
                data = null,
                data_type = that.attr('data-type'),
                option_panel = that.closest('div.sb-option');
            if(confirm(option_panel.attr('data-message-confirm'))) {
                sb_switch_reset_ajax(that, true);
                data = {
                    action: 'sb_ui_reset',
                    data_type: data_type
                };
                $.post(sb_core_admin_ajax.url, data, function(resp){
                    sb_reset_ui_complete(that);
                });
            }
        });
    })();

    (function(){
        $('button.ui-add-item').on('click', function(e){
            e.preventDefault();
            var that = $(this),
                ui_list = that.parent().find('.sb-sortable-list');
            $.post(sb_core_admin_ajax.url, sb_build_add_ui_data(that), function(resp){
                ui_list.append(resp);
            });
        })
    })();

    (function(){
        $('.sb-insert-media').on('click', function(e){
            var that = $(this),
                media_container = that.closest('div.sb-media-upload'),
                image_preview_container = media_container.find('.image-preview'),
                image_container = media_container.find('.image-upload-container'),
                image_input = image_container.find('input'),
                image_url = '';
            if(that.hasClass('delegate')) {
                return;
            }
            e.preventDefault();
            if(file_frame) {
                file_frame.uploader.uploader.param('post_id', new_post_id);
                file_frame.open();
                return;
            }
            file_frame = wp.media({title: 'Insert Media', button:{text: 'Use this image'}, multiple: false});
            file_frame.on('select', function(){
                image_url = window.sb_receive_media_upload(file_frame);
                if($.trim(image_url)) {
                    image_input.val(image_url);
                    image_preview_container.html('<img src="' + image_url + '">');
                    image_preview_container.addClass('has-image');
                }
                file_frame = null;
            });
            file_frame.open();
        });

        $('.sb-remove-media').on('click', function(e){
            var that = $(this),
                media_container = that.closest('div.sb-media-upload'),
                image_preview_container = media_container.find('.image-preview'),
                image_container = media_container.find('.image-upload-container'),
                image_input = image_container.find('input');
            if(that.hasClass('delegate')) {
                return;
            }
            e.preventDefault();
            image_input.val('');
            image_preview_container.removeClass('has-image');
            image_preview_container.html('');
        });

        $('.sb-media-upload .image-url').on('change input', function(e){
            e.preventDefault();
            var that = $(this),
                media_container = that.closest('div.sb-media-upload'),
                image_preview_container = media_container.find('.image-preview'),
                image_text = that.val();
            if(sb_is_url(image_text) && sb_is_image_url(image_text)) {
                image_preview_container.html('<img src="' + image_text + '">');
                image_preview_container.addClass('has-image');
            } else {
                image_preview_container.html('');
                image_preview_container.removeClass('has-image');
            }
        });
    })();

    window.sb_receive_media_upload = function (file_frame) {
        var media_data = file_frame.state().get('selection').first().toJSON();
        old_post_id = wp.media.model.settings.post.id;
        return media_data.url;
    };

})(jQuery);