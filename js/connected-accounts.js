jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        function hocwp_theme_control_disabled_button(button, disabled) {
            disabled = (typeof disabled === "number") ? disabled : 1;

            if (1 === disabled) {
                button.prop("disabled", true);
                button.addClass("disabled");
            } else {
                button.prop("disabled", false);
                button.removeClass("disabled");
            }
        }

        function hocwp_theme_connect_social_ajax(element, ajaxData, successCallback) {
            var defaults = {
                action: "hocwp_theme_connect_social"
            };

            var options = Object.assign({}, defaults, ajaxData);

            $.ajax({
                type: "POST",
                dataType: "json",
                url: hocwpTheme.ajaxUrl,
                data: options,
                success: function (response) {
                    successCallback(response, element);
                },
                complete: function () {
                    hocwp_theme_control_disabled_button(element, 0);
                }
            });
        }

        function hocwp_theme_connect_with_facebook(element, ajaxData, successCallback) {
            var defaults = {
                type: "facebook",
                social_data: "",
                disconnect: 0,
                id: "",
                login: ""
            };

            var options = Object.assign({}, defaults, ajaxData);

            if ("object" == typeof FB) {
                FB.login(function (response) {
                    if (response.authResponse) {
                        FB.api("/me", {fields: "id,name,first_name,last_name,gender,picture,verified,email,birthday"}, function (response) {
                            options.social_data = response;
                            options.id = response.id;
                            hocwp_theme_connect_social_ajax(element, options, successCallback);
                        });
                    }
                }, {scope: "email,public_profile"});
            }
        }

        function hocwp_theme_facebook_login_callback(response) {
            if (response.success) {
                if (response.data && $.trim(response.data.redirect_to)) {
                    window.location.href = response.data.redirect_to;
                } else {
                    var inputRedirectTo = $("input[name='redirect_to']");

                    if (inputRedirectTo.length && $.trim(inputRedirectTo.val())) {
                        window.location.href = inputRedirectTo.val();
                    } else {
                        window.location.reload();
                    }
                }
            } else {
                if ($.trim(response.data.message)) {
                    alert(response.data.message);
                }
            }
        }

        function hocwp_theme_facebook_connect_callback(response, element) {
            if (response.success) {
                var container = element.parent();
                element.text(element.attr("data-disconnect-text"));
                element.attr("data-connect", 1);

                if (response.data.html) {
                    element = element.detach();
                    container.html(response.data.html);
                    container.append(element);
                }
            } else {
                element.text(element.attr("data-text"));
            }
        }

        function hocwp_theme_facebook_disconnect_callback(response, element) {
            if (response.success) {
                var container = element.parent();
                var clone = element.clone();
                element = $(clone);
                element.text(element.attr("data-text"));
                element.attr("data-connect", 0);
                hocwp_theme_control_disabled_button(element, 0);
                container.html(element);
            }
        }

        body.on("click", "button.connect-facebook", function (e) {
            e.preventDefault();

            var element = $(this),
                login = parseInt(element.attr("data-login")),
                connect = parseInt(element.attr("data-connect"));

            hocwp_theme_control_disabled_button(element);

            if (1 === login) {
                hocwp_theme_connect_with_facebook(element, {login: login}, hocwp_theme_facebook_login_callback);
            } else {
                if (0 === connect) {
                    element.text(element.attr("data-loading-text"));
                    hocwp_theme_connect_with_facebook(element, null, hocwp_theme_facebook_connect_callback);
                } else {
                    var options = {
                        type: "facebook",
                        social_data: '',
                        disconnect: 1,
                        id: '',
                        login: ''
                    };

                    hocwp_theme_connect_social_ajax(element, options, hocwp_theme_facebook_disconnect_callback);
                }
            }
        });
    })();
});