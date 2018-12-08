jQuery(document).ready(function ($) {
    (function () {
        $(".update-profile .profile-sidebar").on("click", ".nav-tabs li a", function (e) {
            e.preventDefault();

            var element = $(this),
                item = element.parent(),
                tabs = item.closest(".nav-tabs"),
                tabId = element.attr("href"),
                form = item.closest("form");

            tabs.children("li").removeClass("active");
            item.addClass("active");

            form.attr("data-tab", tabId.replace("#", ""));

            form.find(".tab-pane").removeClass("active");
            form.find(tabId).addClass("active");
        });
    })();
});