(function () {
    var boot = function ($) {
        },
        maxIteration = 1000,
        timeout = setInterval(function () {
            if (window.jQuery && jQuery.ui) {
                clearInterval(timeout);
                boot(jQuery);
            }
            maxIteration--;
            if (maxIteration < 0) {
                clearInterval(timeout);
            }
        }, 50);
}());