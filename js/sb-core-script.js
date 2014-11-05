(function($){

    window.sb_number_format = function(number, separator) {
        var number_string = number.toString(),
            number_len = parseInt(number_string.length),
            last = number_string.slice(-3),
            result = separator + last;
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
        return result;
    };

    // Add default class to external links
    (function(){
        $('a').filter(function() {
            return this.hostname && this.hostname !== location.hostname;
        }).addClass('external');
    })();


})(jQuery);