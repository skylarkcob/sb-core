window.hocwpTheme = window.hocwpTheme || {};
window.hteVIP = window.hteVIP || {};

jQuery(document).ready(function ($) {
    var body = $("body"),
        wizardAddPost = $("#wizardAddPost"),
        progressBar = wizardAddPost.find(".progress .progress-bar"),
        submitButton = wizardAddPost.find("button[type='submit']"),
        confirmOut = true,
        form = $(".add-post-frontend .add-post-form");

    if (form.hasClass("added")) {
        confirmOut = false;
    }

    form.on("submit", function (e) {
        if (!confirm(hteVIP.l10n.confirm_submit_post_message)) {
            e.preventDefault();
            confirmOut = true;
        } else {
            confirmOut = false;
        }

        if (!confirmOut) {
            if (window.removeEventListener) {
                window.removeEventListener("beforeunload", confirmLeaveMessage);
            } else if (window.detachEvent) {
                window.detachEvent("beforeunload", confirmLeaveMessage);
            }
        }
    });

    form.on("click", "button[type='submit'], input[type='submit']", function () {
        confirmOut = false;
    });

    function confirmLeaveMessage(e) {
        var confirmationMessage = hocwpTheme.l10n.beforeUnloadConfirmMessage;

        (e || window.event).returnValue = confirmationMessage;

        return confirmationMessage;
    }

    (function (confirmOut) {
        if (!confirmOut) {
            return false;
        }

        window.addEventListener("beforeunload", confirmLeaveMessage);
    })(confirmOut);

    (function () {
        body.on("HTEAddPostFrontend:FileChange", function (e, input) {
            var element = $(input),
                container = element.closest(".image-button"),
                wrapImage = container.next(".wrap-image"),
                tmpImage = $("#temp_" + element.attr("id"));

            if (tmpImage.length) {
                setTimeout(function () {
                    tmpImage.html(wrapImage.html());
                }, 3000);
            }
        });

        body.on("HTEAddPostFrontend:PostTypeChange", function (e, response) {
            if ("object" == typeof response && response.success) {
                wizardAddPost.find(".preview-taxs").html(response.data.preview_taxs);
            }
        });
    })();

    (function () {
        var hash = window.location.hash;

        if ($.trim(hash)) {
            wizardAddPost.find(".nav-pills > li > a[href='" + hash + "']").trigger("click");
        }

        wizardAddPost.on("click", ".nav-pills > li > a", function (e) {
            e.preventDefault();

            if ("undefined" !== typeof e.originalEvent) {
                return false;
            }
        });

        $(".pager.wizard").on("click", "li a", function (e) {
            e.preventDefault();

            var element = $(this),
                activeItem = wizardAddPost.find(".nav-pills li.active"),
                pagerItem = element.parent(),
                changeItem = null,
                stop = false;

            if (pagerItem.hasClass("disabled")) {
                return false;
            }

            confirmOut = true;

            if (pagerItem.hasClass("next")) {
                var pane = wizardAddPost.find(".tab-pane.active"),
                    requiredFields = pane.find("input[required], input[required='required'], select[required], select[required='required'], textarea[required], textarea[required='required']");

                if (requiredFields.length) {
                    requiredFields.each(function () {
                        var field = $(this),
                            fieldValue = field.val(),
                            tagName = field.prop("tagName");

                        if (!$.trim(fieldValue) || ("SELECT" == tagName && 0 == fieldValue)) {
                            field.focus();
                            stop = true;
                            return false;
                        }
                    });
                }

                if (stop) {
                    submitButton.addClass("disabled");
                    return false;
                }

                var nextItem = activeItem.next();

                changeItem = nextItem;

                pagerItem.prev().removeClass("disabled").children("a").removeClass("disabled");

                if (nextItem.length) {
                    wizardAddPost.find(".nav-pills li").removeClass("active");
                    nextItem.find("a").trigger("click");
                }

                if (nextItem.is(":last-child")) {
                    pagerItem.addClass("disabled");
                    submitButton.removeClass("disabled");
                    confirmOut = false;
                }
            } else {
                var prevItem = activeItem.prev();

                changeItem = prevItem;

                pagerItem.next().removeClass("disabled").children("a").removeClass("disabled");

                if (prevItem.length) {
                    wizardAddPost.find(".nav-pills li").removeClass("active");
                    prevItem.find("a").trigger("click");
                }

                if (prevItem.is(":first-child")) {
                    pagerItem.addClass("disabled");
                }
            }

            var width = parseInt(changeItem.index());

            width++;
            width *= 25;

            progressBar.css({width: width.toString() + "%"});

            element.blur();
        });
    })();

    (function () {
        $("#typeOfPost").on("change", function () {
            var that = this,
                element = $(that),
                type = element.val(),
                desc = $("#" + type + "Desc"),
                container = element.closest(".tab-pane"),
                priceFee = container.find(".price-fee > span"),
                option = container.find("option[value='" + type + "']");

            container.find(".vip-desc").hide();

            wizardAddPost.attr("data-type", type);

            if ("vip" == type) {
                var canPost = parseInt(wizardAddPost.attr("data-can-post"));

                if (0 == canPost) {
                    element.val(element.find("option[value='normal']").attr("value"));
                    alert(hteVIP.l10n.not_enough_coin_message);
                    return false;
                }

                priceFee.removeClass("label-primary");
                priceFee.addClass("label-success");
            } else {
                priceFee.addClass("label-primary");
                priceFee.removeClass("label-success");
            }

            if (desc.length) {
                desc.show();
            }

            priceFee.text(option.attr("data-price"));

            var tmpFee = $("#temp_Fee");

            if (tmpFee.length) {
                tmpFee.html(priceFee.prop("outerHTML"));
            }

            $("#EndDate").trigger("change");
        });
    })();

    (function () {
        body.on("hocwpThemeComboboxInputChange", function (e, value, input) {
            var select = input.parent().prev("select");

            if (select.length) {
                var tmpPreview = wizardAddPost.find("#temp_" + select.attr("id"));

                if (tmpPreview.length && $.trim(value)) {
                    tmpPreview.html(value);
                }
            }
        });

        wizardAddPost.on("change cut paste keyup", "input, select, textarea", function () {
            var that = this,
                element = $(that),
                tagName = element.prop("tagName"),
                text = element.val(),
                tmpPreview = wizardAddPost.find("#temp_" + element.attr("id"));

            if ("SELECT" == tagName) {
                var option = element.find("option[value='" + element.val() + "']");

                text = option.text();
                text = $.trim(text);
            } else if ("INPUT" == tagName) {
                text = $.trim(text);
            }

            if (tmpPreview.length && $.trim(text)) {
                tmpPreview.html(text);
            }
        });

        $("#StartDate, #EndDate").on("change", function () {
            var that = this,
                element = $(that),
                id = element.attr("id"),
                start = element.val(),
                end = element.val(),
                input = null;

            if ("StartDate" == id) {
                input = $("#EndDate");
                end = input.val();
            } else {
                input = $("#StartDate");
                start = input.val();
            }

            var part1 = start.split("/"),
                part2 = end.split("/");

            start = part1[2] + "-" + part1[1] + "-" + part1[0];
            end = part2[2] + "-" + part2[1] + "-" + part2[0];

            var date1 = new Date(start),
                date2 = new Date(end),
                timeDiff = Math.abs(date2.getTime() - date1.getTime()),
                diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)),
                typeOfPost = $("#typeOfPost"),
                vipType = typeOfPost.val(),
                tmpCost = $("#temp_Cost");

            if (!tmpCost || !tmpCost.length) {
                tmpCost = $("#temp_Fee");
            }

            if (tmpCost.length) {
                var formGroup = tmpCost.closest(".form-group"),
                    priceFee = formGroup.find("div.price-fee");

                if ("vip" == vipType) {
                    var cost = parseFloat(typeOfPost.find("option[value='" + vipType + "']").attr("data-cost"));

                    cost *= diffDays;

                    $("#TotalCost").val(cost);

                    cost = cost.toFixed(2);

                    tmpCost.html('<span class="label label-success">$' + cost + '</span>');
                    priceFee.show();
                    priceFee.prev("label").show();
                } else {
                    tmpCost.html("");
                    priceFee.hide();
                    priceFee.prev("label").hide();
                }
            }
        });

        var editor = null,
            interval = setInterval(function () {
                if ("undefined" !== typeof tinyMCE) {
                    editor = tinyMCE.editors["post_content"];

                    if ("undefined" != typeof editor) {
                        editor.on("NodeChange keyup", function () {
                            var tmpContent = $("#temp_post_content");

                            if (tmpContent.length) {
                                tmpContent.html(editor.getContent());
                            }
                        });

                        clearInterval(interval);
                    }
                }
            }, 1000);
    })();
});