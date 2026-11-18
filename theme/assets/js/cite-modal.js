(function () {
  "use strict";

  var popover, fieldEl, cachedText = null, openTrigger = null;

  function stripHtml(html) {
    var tmp = document.createElement("div");
    tmp.innerHTML = html;
    return tmp.textContent.replace(/\s+/g, " ").trim();
  }

  function buildPopover() {
    popover = document.createElement("div");
    popover.className = "aoc-cite-popover";
    popover.hidden = true;
    popover.setAttribute("role", "dialog");
    popover.setAttribute("aria-label", "How to cite this project");

    popover.innerHTML =
      '<div class="aoc-cite-field">Loading&hellip;</div>' +
      '<button type="button" class="aoc-cite-copy" data-tooltip="Copy to clipboard" aria-label="Copy to clipboard">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>' +
      '</button>';

    document.body.appendChild(popover);
    fieldEl = popover.querySelector(".aoc-cite-field");

    popover.querySelector(".aoc-cite-copy").addEventListener("click", function () {
      var btn = this;
      navigator.clipboard.writeText(fieldEl.textContent.trim()).then(function () {
        btn.classList.add("is-copied");
        setTimeout(function () { btn.classList.remove("is-copied"); }, 1600);
      });
    });

    document.addEventListener("click", function (e) {
      if (popover.hidden) return;
      if (popover.contains(e.target) || (openTrigger && openTrigger.contains(e.target))) return;
      close();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !popover.hidden) close();
    });
    window.addEventListener("resize", function () {
      if (!popover.hidden) position();
    });
    window.addEventListener("scroll", function () {
      if (!popover.hidden) position();
    }, true);
  }

  function position() {
    var rect = openTrigger.getBoundingClientRect();
    var popRect = popover.getBoundingClientRect();
    var left = rect.left + rect.width / 2 - popRect.width / 2;
    left = Math.max(8, Math.min(left, window.innerWidth - popRect.width - 8));
    popover.style.top = (rect.bottom + 10) + "px";
    popover.style.left = left + "px";
  }

  function open(trigger) {
    if (!popover) buildPopover();
    openTrigger = trigger;
    popover.hidden = false;
    position();

    if (cachedText !== null) {
      fieldEl.textContent = cachedText;
      position();
      return;
    }

    fetch("/wp-json/wp/v2/pages?slug=cite")
      .then(function (res) { return res.json(); })
      .then(function (pages) {
        var html = (pages && pages[0] && pages[0].content && pages[0].content.rendered) || "";
        cachedText = html ? stripHtml(html) : "Citation unavailable.";
        fieldEl.textContent = cachedText;
        position();
      })
      .catch(function () {
        fieldEl.textContent = "Couldn’t load the citation.";
        position();
      });
  }

  function close() {
    if (popover) popover.hidden = true;
    openTrigger = null;
  }

  document.addEventListener("click", function (e) {
    var trigger = e.target.closest(".aoc-cite-trigger a, a.aoc-cite-trigger");
    if (!trigger) return;
    e.preventDefault();
    if (openTrigger === trigger && popover && !popover.hidden) {
      close();
    } else {
      open(trigger);
    }
  });
})();
