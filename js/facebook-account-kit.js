jQuery(document).ready(function ($) {
    if ("undefined" === typeof hteFacebookAccountKit) {
        return false;
    }

    var body = $("body"),
        accountKitInit = false;

    (function () {
        body.on("click", ".facebook-account-kit button.sms-or-email", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                container = element.parent(),
                popup = container.next(".fac-popup");

            popup.toggleClass("active");
        });

        body.on("click", ".fac-popup button.close-btn", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                popup = element.closest(".fac-popup");

            popup.toggleClass("active");
        });
    })();

    function callbackHelper(response, do_action, user_id) {
        do_action = do_action || "login";
        user_id = user_id || "";

        if (response.status === "PARTIALLY_AUTHENTICATED") {
            var ajaxOverlay = body.find(".hocwp-theme.ajax-overlay");

            if (!ajaxOverlay || !ajaxOverlay.length) {
                body.append(hocwpTheme.ajaxOverlay);
                ajaxOverlay = body.find(".hocwp-theme.ajax-overlay")
            }

            body.trigger("hocwpTheme:ajaxStart");

            $.post(hocwpTheme.ajaxUrl, {
                action: "hocwp_theme_ajax",
                callback: "hocwp_extension_account_connect_facebook_account_kit_ajax_callback",
                code: response.code,
                csrf: response.state,
                app_id: hteFacebookAccountKit.app_id,
                nonce: hocwpTheme.nonce,
                app_secret: hteFacebookAccountKit.app_secret,
                api_version: hteFacebookAccountKit.api_version,
                do_action: do_action,
                user_id: user_id
            }, function (response) {
                body.trigger("hocwpTheme:ajaxComplete");

                if (response.data && response.data.message && $.trim(response.data.message)) {
                    alert(response.data.message);
                }

                if (response.success) {
                    if (response.data && response.data.redirect_to && $.trim(response.data.redirect_to)) {
                        window.location.href = response.data.redirect_to;
                    } else {
                        window.location.reload();
                    }
                }
            });
        } else if (response.status === "NOT_AUTHENTICATED") {
            console.log(hteFacebookAccountKit.status_NOT_AUTHENTICATED);
        } else if (response.status === "BAD_PARAMS") {
            console.log(hteFacebookAccountKit.status_BAD_PARAMS);
        }
    }

    function loginCallback(response) {
        callbackHelper(response);
    }

    function connectEmailCallback(response) {
        callbackHelper(response, "connect-email", $("input[name='user_id']").val());
    }

    function connectPhoneCallback(response) {
        callbackHelper(response, "connect-phone", $("input[name='user_id']").val());
    }

    (function () {
        AccountKit_OnInteractive = function () {
            AccountKit.init({
                appId: hteFacebookAccountKit.app_id,
                state: hteFacebookAccountKit.nonce,
                version: hteFacebookAccountKit.api_version,
                display: hteFacebookAccountKit.display,
                fbAppEventsEnabled: true,
                debug: hteFacebookAccountKit.debug
            });
        };

        var count = 0,
            interval = setInterval(function () {
                if ("undefined" !== typeof AccountKit_OnInteractive.hasRun && true === AccountKit_OnInteractive.hasRun) {
                    accountKitInit = true;
                    clearInterval(interval);
                }

                count++;
            }, 500);

        function smsConnect(phoneNumber) {
            phoneNumber = phoneNumber || "";

            AccountKit.login("PHONE", {
                countryCode: "+" + hteFacebookAccountKit.country_code,
                phoneNumber: phoneNumber
            }, loginCallback);
        }

        function emailConnect(emailAddress) {
            emailAddress = emailAddress || "";

            AccountKit.login("EMAIL", {emailAddress: emailAddress}, loginCallback);
        }

        body.on("click", ".fac-popup.verify-box .buttons button", function (e) {
            e.preventDefault();

            if (!accountKitInit) {
                return false;
            }

            var that = this,
                element = $(that),
                id = element.attr("id"),
                valid = false;

            if ("verify-phone" === id) {
                valid = true;
                smsConnect();
            } else if ("verify-email" === id) {
                emailConnect();
                valid = true;
            }

            if (valid && "modal" == hteFacebookAccountKit.display) {
                body.find(".fac-popup.verify-box").toggleClass("active");
            }
        });

        // Connect or disconnect phone number or email address on admin profile page.
        if (body.hasClass("profile-php") && body.hasClass("wp-admin")) {
            $(hteFacebookAccountKit.connect_email_button).insertAfter(body.find("td input[name='email']"));
            $(hteFacebookAccountKit.connect_phone_button).insertAfter(body.find("td input[name='phone']"));

            setTimeout(function () {
                if ("undefined" === typeof AccountKit_OnInteractive.hasRun || true !== AccountKit_OnInteractive.hasRun) {
                    AccountKit_OnInteractive();
                    accountKitInit = true;
                }
            }, 1000);

            // Connect or disconnect email address.
            body.on("click", "td input[name='email'] + button.connect-disconnect", function (e) {
                e.preventDefault();

                var that = this,
                    element = $(that),
                    action = element.data("action"),
                    input = element.prev("input[name='email']"),
                    email = input.val();

                if ("disconnect-email" == action) {
                    if (confirm(hteFacebookAccountKit.confirm_disconnect_email)) {
                        $.post(hocwpTheme.ajaxUrl, {
                            action: "hocwp_theme_ajax",
                            callback: "hocwp_extension_account_connect_facebook_account_kit_ajax_callback",
                            do_action: action,
                            email: email,
                            nonce: hocwpTheme.nonce
                        }, function (response) {
                            body.trigger("hocwpTheme:ajaxDone", [element]);

                            if (response.data && response.data.message && $.trim(response.data.message)) {
                                alert(response.data.message);
                            }

                            if (response.success) {
                                window.location.reload();
                            }
                        });
                    } else {
                        body.trigger("hocwpTheme:ajaxDone", [element]);
                    }
                } else if ("connect-email" == action) {
                    body.trigger("hocwpTheme:ajaxDone", [element]);

                    if (!accountKitInit) {
                        return false;
                    }

                    AccountKit.login("EMAIL", {emailAddress: email}, connectEmailCallback);
                }
            });

            // Connect or disconnect phone number.
            body.on("click", "td input[name='phone'] + button.connect-disconnect", function (e) {
                e.preventDefault();

                var that = this,
                    element = $(that),
                    action = element.data("action"),
                    input = element.prev("input[name='phone']"),
                    phone = input.val();

                if ("disconnect-phone" == action) {
                    if (confirm(hteFacebookAccountKit.confirm_disconnect_phone)) {
                        $.post(hocwpTheme.ajaxUrl, {
                            action: "hocwp_theme_ajax",
                            callback: "hocwp_extension_account_connect_facebook_account_kit_ajax_callback",
                            do_action: action,
                            phone: phone,
                            nonce: hocwpTheme.nonce,
                            user_id: $("input[name='user_id']").val()
                        }, function (response) {
                            body.trigger("hocwpTheme:ajaxDone", [element]);

                            if (response.data && response.data.message && $.trim(response.data.message)) {
                                alert(response.data.message);
                            }

                            if (response.success) {
                                window.location.reload();
                            }
                        });
                    } else {
                        body.trigger("hocwpTheme:ajaxDone", [element]);
                    }
                } else if ("connect-phone" == action) {
                    body.trigger("hocwpTheme:ajaxDone", [element]);

                    if (!$.trim(phone)) {
                        input.focus();
                        //element.addClass("disabled");
                        //element.attr("disabled", "disabled");
                    } else {
                        if (!accountKitInit) {
                            return false;
                        }

                        AccountKit.login("PHONE", {
                            countryCode: "+" + hteFacebookAccountKit.country_code,
                            phoneNumber: phone
                        }, connectPhoneCallback);
                    }
                }
            });
        }
    })();
});