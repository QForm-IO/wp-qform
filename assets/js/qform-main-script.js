(function () {
    function init() {
        const scr = document.createElement("script");
        scr.type = "text/javascript";
        scr.async = "async";
        scr.defer = true
        scr.src =
            "//cdn.qform.io/forms.js?v=" + new Date().getTime() / 1000;
        const scrInsert = document.getElementsByTagName("script")[0];
        scrInsert.parentNode.insertBefore(scr, scrInsert);
    }

    const d = document;
    const w = window;
    if (d.readyState === "interactive") {
        init();
    } else {
        if (w.attachEvent) {
            w.attachEvent("onload", init);
        } else {
            w.addEventListener("DOMContentLoaded", init, false);
        }
    }
})();