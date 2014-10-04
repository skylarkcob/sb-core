(function($){
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