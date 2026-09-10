/* ==========================================================================
   SynchWeb / ISPyB User Guide - screenshot lightbox
   LS-CAT | Life Sciences Collaborative Access Team

   Makes every screenshot card (.guide-figure, see synchwebGuide.css) and
   every filter-type example card (.info-card[data-zoom-img]) clickable,
   opening a full-size zoomed view in a lightweight overlay. The whole
   card is the click target -- not just the image pixels -- so the
   "click to enlarge" hint always sits over live, clickable area.
   No dependencies. Keyboard accessible: Enter/Space opens, Esc closes,
   focus returns to the card on close.
   ========================================================================== */
(function () {
    function ready(fn) {
        if (document.readyState !== "loading") fn();
        else document.addEventListener("DOMContentLoaded", fn);
    }

    ready(function () {
        var figures = document.querySelectorAll(".guide-figure:not(.placeholder)");
        var zoomCards = document.querySelectorAll(".info-card[data-zoom-img]");
        if (!figures.length && !zoomCards.length) return;

        var overlay = document.createElement("div");
        overlay.className = "guide-lightbox";
        overlay.setAttribute("role", "dialog");
        overlay.setAttribute("aria-modal", "true");
        overlay.setAttribute("aria-hidden", "true");
        overlay.innerHTML =
            '<button type="button" class="guide-lightbox-close" aria-label="Close">&times;</button>' +
            '<img alt="">' +
            '<p class="guide-lightbox-caption"></p>';
        document.body.appendChild(overlay);

        var imgEl = overlay.querySelector("img");
        var capEl = overlay.querySelector(".guide-lightbox-caption");
        var closeBtn = overlay.querySelector(".guide-lightbox-close");
        var lastFocused = null;

        function openLightbox(src, alt, caption) {
            lastFocused = document.activeElement;
            imgEl.src = src;
            imgEl.alt = alt || "";
            capEl.textContent = caption || "";
            capEl.style.display = caption ? "" : "none";
            overlay.classList.add("open");
            overlay.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
            closeBtn.focus();
        }

        function closeLightbox() {
            overlay.classList.remove("open");
            overlay.setAttribute("aria-hidden", "true");
            imgEl.src = "";
            document.body.style.overflow = "";
            if (lastFocused && typeof lastFocused.focus === "function") {
                lastFocused.focus();
            }
        }

        overlay.addEventListener("click", function (e) {
            if (e.target === overlay) closeLightbox();
        });
        closeBtn.addEventListener("click", closeLightbox);
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && overlay.classList.contains("open")) {
                closeLightbox();
            }
        });

        // Wires up a single element as a lightbox trigger. getData() is
        // called fresh on every open so it can read whatever's current on
        // the element (src/alt/caption for a <figure>, or data- attributes
        // for an info-card).
        function makeZoomable(el, getData) {
            el.tabIndex = 0;
            el.setAttribute("role", "button");
            var initial = getData();
            el.setAttribute("aria-label", "Click to enlarge: " + (initial.alt || "screenshot"));

            if (!el.querySelector(".zoom-hint")) {
                var hint = document.createElement("span");
                hint.className = "zoom-hint";
                hint.setAttribute("aria-hidden", "true");
                hint.textContent = "🔍 Click to enlarge";
                el.appendChild(hint);
            }

            function open() {
                var d = getData();
                openLightbox(d.src, d.alt, d.caption);
            }

            el.addEventListener("click", function (e) {
                // Let caption text stay selectable rather than always
                // popping the lightbox when someone is trying to copy it.
                if (e.target.closest("figcaption")) return;
                open();
            });
            el.addEventListener("keydown", function (e) {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    open();
                }
            });
        }

        figures.forEach(function (figure) {
            var img = figure.querySelector("img");
            if (!img) return;

            makeZoomable(figure, function () {
                var figcap = figure.querySelector("figcaption");
                return {
                    src: img.src,
                    alt: img.alt,
                    caption: figcap ? figcap.textContent.trim() : ""
                };
            });
        });

        zoomCards.forEach(function (card) {
            makeZoomable(card, function () {
                return {
                    src: card.getAttribute("data-zoom-img") || "",
                    alt: card.getAttribute("data-zoom-alt") || "",
                    caption: card.getAttribute("data-zoom-caption") || ""
                };
            });
        });
    });
})();
