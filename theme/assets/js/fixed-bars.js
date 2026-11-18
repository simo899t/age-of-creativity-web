(function () {
  "use strict";

  // Publishes the footer's real rendered height as a CSS var so
  // .wp-site-blocks can reserve that much padding (the footer is
  // position:fixed, see theme.css, and its height changes with viewport
  // width as content wraps).
  function setVars() {
    var footer = document.querySelector(".wp-site-blocks > footer");
    if (footer) {
      document.documentElement.style.setProperty("--aoc-footer-h", footer.offsetHeight + "px");
    }
  }

  setVars();
  window.addEventListener("load", setVars);
  window.addEventListener("resize", setVars);
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(setVars);
  }
})();
