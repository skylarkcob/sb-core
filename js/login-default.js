window.hocwpLogin = window.hocwpLogin || {};

var headline = document.getElementsByTagName("h1")[0];

if (headline) {
    var link = headline.getElementsByTagName("a")[0];

    if (link) {
        link.innerHTML = hocwpLogin.logo;
        headline.style.display = "block";
    }
}