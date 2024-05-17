window.hocwpTheme = window.hocwpTheme || {};
window.wp = window.wp || {};

jQuery(document).ready(function ($) {
    const body = $("body");

    var frame = "",
        frameEdit = "",
        frameThumb = "",
        frameThumbEdit = "",
        selection = [];

    function __open_frame_edit(f, i) {
        f.on("open", function () {
            if (i && $.isNumeric(i)) {
                f.state().get("selection").add(wp.media.attachment(i));
            }
        }).open();
    }

    // Delete slide item
    (function () {
        body.on("click", ".slide-item .item-header button.delete-slide", function (e) {
            e.preventDefault();

            if (confirm(hocwpTheme.l10n.confirmDeleteMessage)) {
                var item = $(this).closest("div.slide-item");

                item.fadeOut(1000);

                setTimeout(function () {
                    if (item && item.length) {
                        item.remove();
                    }
                }, 1500);
            }
        });
    })();

    // Change slide image
    (function () {
        body.on("click", ".slide-items .items .slide-item img.slide-image, .slide-item .item-header button.update-image", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                imageId = element.attr("data-image-id"),
                slideItem = element.closest("div.slide-item");

            if ("object" !== typeof frameEdit) {
                frameEdit = wp.media({
                    title: HTESlider.text.add_media_title,
                    multiple: false
                });
            }

            __open_frame_edit(frameEdit, imageId);

            frameEdit.on("select", function (e) {
                var item = frameEdit.state().get("selection").first();

                item = item.toJSON();

                if (item.id !== imageId) {
                    var slideImage = slideItem.find(".item-body > img[data-image-id='" + imageId + "']");

                    slideItem.find("*[data-image-id='" + imageId + "']").attr("data-image-id", item.id);
                    slideItem.find("input[value='" + imageId + "'].image-id").val(item.id);
                    slideImage.attr("src", item.url).attr("alt", item.title).attr("title", item.title);
                }

                frameEdit = "";
            });
        });
    })();

    function __update_thumbnail_image(el, image, id) {
        var thumbnail = el.parent();

        image = image || "";
        id = id || "";

        if ("IMG" === el.prop("tagName")) {
            thumbnail = el.closest(".thumbnail");
        } else {
            el.hide();
        }

        thumbnail.find(".thumb-image").html(image);
        thumbnail.find("input.thumbnail-id").val(id);

        if (el.hasClass("set-image")) {
            thumbnail.addClass("has-image");
            thumbnail.find("a.remove-image").show();
        } else {
            thumbnail.removeClass("has-image");
            thumbnail.find("a.set-image").show();
        }
    }

    // Add slide thumbnail
    (function () {
        body.on("click", ".thumbnail a.set-image", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that);

            if ("object" !== typeof frameThumb) {
                frameThumb = wp.media({
                    title: HTESlider.text.add_media_title,
                    multiple: false
                });
            }

            frameThumb.on("select", function (e) {
                var item = frameThumb.state().get("selection").first();
                item = item.toJSON();

                __update_thumbnail_image(element, "<img src='" + item.url + "' alt='" + item.title + "' data-id='" + item.id + "'>", item.id);

                frameThumb = "";
            }).open();
        });
    })();

    // Update slide thumbnail
    (function () {
        body.on("click", ".thumbnail .thumb-image img", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                imageId = element.closest(".thumbnail").find("input.thumbnail-id").val();

            if ("object" !== typeof frameThumbEdit) {
                frameThumbEdit = wp.media({
                    title: HTESlider.text.add_media_title,
                    multiple: false
                });
            }

            __open_frame_edit(frameThumbEdit, imageId);

            frameThumbEdit.on("select", function (e) {
                var item = frameThumbEdit.state().get("selection").first();
                item = item.toJSON();

                __update_thumbnail_image(element, "<img src='" + item.url + "' alt='" + item.title + "' data-id='" + item.id + "'>", item.id);

                frameThumbEdit = "";
            });
        });
    })();

    // Remove slide thumbnail
    (function () {
        body.on("click", ".thumbnail a.remove-image", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that);

            if (confirm(hocwpTheme.l10n.confirmDeleteMessage)) {
                __update_thumbnail_image(element);
            }
        });
    })();

    // Add slide items
    (function () {
        body.on("click", "#addSlideItems", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                slideItems = element.prev(".items");

            if ("object" !== typeof frame) {
                frame = wp.media({
                    title: HTESlider.text.add_media_title,
                    multiple: true
                });
            }

            frame.on("select", function (e) {
                selection = frame.state().get("selection");

                selection.forEach(function (item) {
                    var html = HTESlider.slideItem,
                        key_index = slideItems.children().length;

                    item = item.toJSON();
                    html = html.replace(/\%image_url%/g, item.url);
                    html = html.replace(/\%image_title%/g, item.title);
                    html = html.replace(/\%image_id%/g, item.id);
                    html = html.replace(/\%slide_id%/g, element.attr("data-slide-id"));
                    html = html.replace(/\%key_index%/g, key_index);

                    slideItems.append(html);
                });

                slideItems.sortable();
                frame = "";
            }).open();
        });
    })();

    // Sortable slide items
    (function () {
        $(".slider-items .items").sortable();
    })();
});