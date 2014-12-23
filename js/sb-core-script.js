(function($){
    window.sb_is_array = function(variable){
        if((Object.prototype.toString.call(variable) === '[object Array]')) {
            return true;
        }
        return false;
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


})(jQuery);