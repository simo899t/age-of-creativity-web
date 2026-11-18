(function () {
  "use strict";

  document.addEventListener("click", function (e) {
    var btn = e.target.closest(".aoc-copy-btn");
    if (!btn) return;

    var target = document.querySelector(btn.getAttribute("data-copy-target"));
    if (!target) return;

    navigator.clipboard.writeText(target.innerText.trim()).then(function () {
      btn.classList.add("is-copied");
      setTimeout(function () {
        btn.classList.remove("is-copied");
      }, 2000);
    });
  });
})();
