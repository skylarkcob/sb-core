jQuery(document).ready(function ($) {
    var body = $("body"),
        form = $(".add-post-frontend .add-post-form"),
        cache = {};

    function split(val) {
        return val.split(/,\s*/);
    }

    function extractLast(term) {
        return split(term).pop();
    }

    function hocwpAddPostFrontendAjaxTags(element) {
        element.on("keydown", function (event) {
            if (event.keyCode === $.ui.keyCode.TAB &&
                $(this).autocomplete("instance").menu.active) {
                event.preventDefault();
            }
        }).autocomplete({
            source: function (request, response) {
                var term = extractLast(request.term);

                if (term in cache) {
                    response(cache[term]);
                    return;
                }

                $.getJSON(hocwpTheme.homeUrl + "?return=name&get_terms=" + element.data("taxonomy") + "&_wpnonce=" + hocwpTheme.nonce, {
                    term: term
                }, function (data) {
                    cache[term] = data;
                    response(data);
                });
            },
            search: function () {
                var term = extractLast(this.value);

                if (term.length < 2) {
                    return false;
                }
            },
            focus: function () {
                return false;
            },
            select: function (event, ui) {
                var terms = split(this.value);

                terms.pop();
                terms.push(ui.item.value);
                terms.push("");
                this.value = terms.join(", ");

                return false;
            },
            minLength: 2,
            open: function () {
                $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            close: function () {
                $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        });
    }

    (function () {
        form.on("change", "select[name='add_post_type']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                post_type = element.val(),
                rightLabel = form.find(".add-post-wizard.right-label");

            $.ajax({
                type: "POST",
                dataType: "json",
                url: hocwpTheme.ajaxUrl,
                data: {
                    action: "hte_add_post_frontend_change_post_type",
                    post_type: post_type,
                    right_label: rightLabel.length
                },
                success: function (response) {
                    if (response.success) {
                        form.find(".hierarchical-taxs").html(response.data.hierarchical);
                        form.find(".none-hierarchical-taxs").html(response.data.none_hierarchical);
                    } else {
                        element.val($.data(that, "current"));
                        return false;
                    }

                    body.trigger("HTEAddPostFrontend:PostTypeChange", [response]);
                }
            });
        });

        body.on("HTEAddPostFrontend:PostTypeChange", function (e, response) {
            if ("object" == typeof response && response.success) {
                cache = {};

                form.find("select[data-combobox='1']").combobox();

                form.find("input.nonhierarchical-taxonomy").each(function () {
                    var that = this,
                        element = $(that);

                    element.val("");

                    hocwpAddPostFrontendAjaxTags(element);
                });
            }
        });
    })();

    (function () {
        $("input.nonhierarchical-taxonomy").each(function () {
            var that = this,
                element = $(that);

            hocwpAddPostFrontendAjaxTags(element);
        });
    })();

    (function () {
        form.on("click", ".image-button", function () {
            var element = $(this),
                container = element.parent(),
                input = container.find("input[type='file']").first();

            if (!input.prop("multiple")) {
                input.get(0).click();
            }
        });

        form.on("change", ".image-button input[type='file']", function () {
            var that = this,
                element = $(that),
                filename = element.val(),
                extension = filename.split(".").pop(),
                files = that.files,
                container = element.closest(".image-button"),
                wrapImage = container.next(".wrap-image");

            if (element.prop("multiple")) {
                for (var j = 0; j < files.length; j++) {
                    filename = files[j].name;
                    extension = filename.split(".").pop();

                    if ("png" !== extension && "jpg" !== extension && "jpeg" !== extension) {
                        element.val("");
                        alert(hteAddPostFrontend.l10n.invalidImageMessage);

                        if (wrapImage.length) {
                            wrapImage.html("");
                        }

                        body.trigger("HTEAddPostFrontend:FileChange", [element]);

                        return false;
                    }
                }
            } else if ("png" !== extension && "jpg" !== extension && "jpeg" !== extension) {
                element.val("");
                alert(hteAddPostFrontend.l10n.invalidImageMessage);

                if (wrapImage.length) {
                    wrapImage.html("");
                }

                body.trigger("HTEAddPostFrontend:FileChange", [element]);

                return false;
            }

            if (wrapImage.length) {
                wrapImage.html("");

                if (files) {
                    for (var i = 0; i < files.length; i++) {
                        var reader = new FileReader();

                        reader.onload = function (e) {
                            var image = '<img src="' + e.target.result + '" alt="">';

                            wrapImage.append(image);
                        };

                        reader.readAsDataURL(files[i]);
                    }
                }
            }

            body.trigger("HTEAddPostFrontend:FileChange", [element]);
        });
    })();
});