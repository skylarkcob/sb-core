window.sb_core = window.sb_core || {};

var sb_password_strength,
    sb_refresh,
    sb_resize_iframe,
    sb_ajax_loader;

(function($){
    window.sb_is_array = function(variable){
        if((Object.prototype.toString.call(variable) === '[object Array]')) {
            return true;
        }
        return false;
    };

    sb_core.sb_refresh = function() {
        window.location.href = window.location.href;
    };

    sb_core.sb_ajax_loader = function(status) {
        var ajax_loader = $('div.sb-ajax-loader');
        if(status) {
            ajax_loader.addClass('active');
        } else {
            ajax_loader.removeClass('active');
        }
    };

    sb_core.sb_resize_iframe = function(obj, divisor, min_height) {
        divisor = divisor || 1;
        min_height = min_height || 100;
        var height = obj.contentWindow.document.body.offsetHeight;
        height /= divisor;
        $(obj).css({'height' : height + 'px', 'min-height' : min_height + 'px'});
    };

    sb_core.sb_password_strength = function($pass1, $pass2, $strengthResult, $submitButton, blacklistArray) {
        var pass1 = $pass1.val(),
            pass2 = $pass2.val(),
            strength = 0;
        if(!$.trim(pass1)) {
            return;
        }
        $submitButton.attr('disabled', 'disabled');
        $strengthResult.removeClass('short bad good strong');
        blacklistArray = blacklistArray.concat(wp.passwordStrength.userInputBlacklist());
        strength = wp.passwordStrength.meter(pass1, blacklistArray, pass2);
        switch(strength) {
            case 2:
                $strengthResult.addClass('bad').html(pwsL10n.bad);
                break;
            case 3:
                $strengthResult.addClass('good').html(pwsL10n.good);
                break;
            case 4:
                $strengthResult.addClass('strong').html(pwsL10n.strong);
                break;
            case 5:
                $strengthResult.addClass('short').html(pwsL10n.mismatch);
                break;
            default:
                $strengthResult.addClass('short').html(pwsL10n.short);
        }
        if (3 <= strength && pass1 == pass2) {
            $submitButton.removeAttr('disabled');
        }
        return strength;
    };

    window.sb_set_cookie = function(cname, cvalue, exmin) {
        var d = new Date();
        d.setTime(d.getTime() + (exmin * 60 * 1000));
        var expires = "expires=" + d.toGMTString(),
            my_cookies = cname + "=" + cvalue + "; " + expires + "; path=/";
        document.cookie = my_cookies;
    };

    window.sb_stop_mouse_wheel = function(e) {
        if(!e) {
            e = window.event;
        }
        if(e.preventDefault) {
            e.preventDefault();
        }
        e.returnValue = false;
    };

    window.sb_number_format = function(number, separator, currency) {
        currency = currency || '₫';
        separator = separator || ',';
        var number_string = number.toString(),
            decimal = '.',
            numbers = number_string.split('.'),
            number_len = 0,
            last = '',
            result = '';
        if(!window.sb_is_array(numbers)) {
            numbers = number_string.split(',');
            decimal = ',';
        }
        if(window.sb_is_array(numbers)) {
            number_string = numbers[0];
        }
        number_len = parseInt(number_string.length);
        last = number_string.slice(-3);
        if(number_len > 3) {
            result += separator + last;
        } else {
            result += last;
        }

        while(number_len > 3) {
            number_len -= 3;
            number_string = number_string.slice(0, number_len);
            last = number_string.slice(-3)

            if(number_len <= 3) {
                result = last + result;
            } else {
                result = separator + last + result;
            }
        }
        if(window.sb_is_array(numbers) && $.isNumeric(numbers[1])) {
            result += decimal + numbers[1];
        }
        result += currency;
        result = $.trim(result);
        return result;
    };

    // Add default class to external links
    (function(){
        $('a').filter(function() {
            return this.hostname && this.hostname !== location.hostname;
        }).addClass('external');
    })();

    (function(){
        $('.sb-captcha .reload, .sb-captcha-image').on('click', function(e){
            e.preventDefault();

            var that = $(this),
                captcha = that.parent().find('.captcha-code'),
                data = null;
            if(that.hasClass('disabled')) {
                return;
            }
            captcha.css({opacity: 0.2});
            data = {
                'action': 'sb_reload_captcha',
                len: captcha.attr('data-len')
            };
            that.addClass('disabled');
            $.post(sb_core_ajax.url, data, function(resp){
                captcha.attr('src', resp);
                that.removeClass('disabled');
                captcha.css({opacity: 1});
            });
        });
    })();

})(jQuery);