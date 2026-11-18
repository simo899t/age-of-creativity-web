/* Creators timeline: turns the server-rendered list into an interactive
   chart. Each creator is a line from birth to death, grouped by field.
   Point at (or focus) a line to see the person and their connections;
   select it to pin a card with the details. No dependencies. */
(function () {
  "use strict";

  var SVG_NS = "http://www.w3.org/2000/svg";
  var PAD_X = 28;
  var MIN_WIDTH = 920;
  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function h(tag, cls, text) {
    var el = document.createElement(tag);
    if (cls) el.className = cls;
    if (text != null) el.textContent = text;
    return el;
  }

  function norm(s) {
    return (s || "").toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "");
  }

  function init(root) {
    var dataEl = root.querySelector(".aoc-tl-data");
    if (!dataEl) return;
    var data;
    try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }
    if (!data.people || !data.people.length) return;

    var stage = root.querySelector(".aoc-tl-stage");
    var canvas = root.querySelector(".aoc-tl-canvas");
    var detail = root.querySelector(".aoc-tl-detail");
    var hint = detail.querySelector(".aoc-tl-hint");
    var hintText = hint ? hint.textContent : "";
    var fieldButtons = Array.prototype.slice.call(root.querySelectorAll(".aoc-tl-field"));
    var search = root.querySelector(".aoc-tl-search input");

    var fields = {};
    data.fields.forEach(function (f) { fields[f.slug] = f; });
    var byId = {};
    data.people.forEach(function (p) { byId[p.id] = p; p.links = []; });
    // Connections are stored on one side only; make them symmetric.
    data.people.forEach(function (p) {
      (p.connections || []).forEach(function (id) {
        var q = byId[id];
        if (!q || q === p) return;
        if (p.links.indexOf(q.id) === -1) p.links.push(q.id);
        if (q.links.indexOf(p.id) === -1) q.links.push(p.id);
      });
    });

    var state = { field: "", query: "", hover: null, selected: null };

    /* ---------- build DOM ---------- */
    canvas.innerHTML = "";
    canvas.classList.add("is-ready");
    var plot = h("div", "aoc-tl-plot");
    var links = document.createElementNS(SVG_NS, "svg");
    links.setAttribute("class", "aoc-tl-links");
    links.setAttribute("aria-hidden", "true");
    plot.appendChild(links);
    var bandLayer = h("div", "aoc-tl-bands");
    bandLayer.setAttribute("aria-hidden", "true");
    plot.appendChild(bandLayer);
    var axis = h("div", "aoc-tl-axis");
    axis.setAttribute("aria-hidden", "true");
    var eras = h("div", "aoc-tl-eras");
    var tip = h("div", "aoc-tl-tip");
    tip.setAttribute("aria-hidden", "true");
    canvas.appendChild(plot);
    canvas.appendChild(axis);
    canvas.appendChild(eras);
    canvas.appendChild(tip);

    var nodes = {};
    data.people.forEach(function (p) {
      var b = h("button", "aoc-tl-life");
      b.type = "button";
      var f = fields[p.field];
      b.style.setProperty("--c", f ? f.color : "#6b665e");
      b.setAttribute("aria-label", p.name + ", " + years(p) + (f ? ", " + f.name : ""));
      b.appendChild(h("span", "aoc-tl-name", p.name));
      b.appendChild(h("span", "aoc-tl-bar"));
      b.addEventListener("mouseenter", function () { setHover(p.id); });
      b.addEventListener("mouseleave", function () { setHover(null); });
      b.addEventListener("focus", function () { setHover(p.id); });
      b.addEventListener("blur", function () { setHover(null); });
      b.addEventListener("click", function () { select(state.selected === p.id ? null : p.id); });
      plot.appendChild(b);
      nodes[p.id] = b;
    });

    /* ---------- helpers ---------- */
    function years(p) {
      return p.born + "–" + (p.died == null ? "" : p.died);
    }

    var measureCtx = document.createElement("canvas").getContext("2d");
    function textWidth(s) {
      measureCtx.font = "500 12.5px " + (getComputedStyle(root).getPropertyValue("--aoc-ui-font") || "sans-serif");
      return measureCtx.measureText(s).width;
    }

    function matches(p) {
      if (!state.query) return true;
      var q = norm(state.query);
      return norm(p.name).indexOf(q) !== -1 || norm(p.description).indexOf(q) !== -1;
    }

    function visible() {
      return data.people.filter(function (p) { return !state.field || p.field === state.field; });
    }

    /* ---------- layout ---------- */
    var geo = {};
    var width = 0;

    function x(year) {
      var t = (year - data.start) / (data.end - data.start);
      return PAD_X + t * (width - PAD_X * 2);
    }

    // Greedy interval packing: each person goes in the first lane where
    // they don't overlap anyone (including the space their name needs).
    function pack(list, labelled) {
      var lanes = [];
      var placed = [];
      list.slice().sort(function (a, b) { return a.born - b.born; }).forEach(function (p) {
        var x0 = Math.max(x(p.born), x(data.start));
        var x1 = Math.min(x(p.died == null ? data.end : p.died), x(data.end));
        var reach = labelled ? Math.max(x1, x0 + textWidth(p.name) + 6) : x1;
        var lane = 0;
        while (lane < lanes.length && lanes[lane] > x0 - (labelled ? 14 : 6)) lane++;
        lanes[lane] = reach;
        placed.push({ p: p, lane: lane, x0: x0, x1: x1 });
      });
      return { lanes: lanes.length, placed: placed };
    }

    function layout() {
      width = Math.max(stage.clientWidth, MIN_WIDTH);
      canvas.style.width = width + "px";
      var list = visible();
      var groups = [];
      if (state.field) {
        groups.push({ field: fields[state.field], people: list });
      } else {
        data.fields.forEach(function (f) {
          var ps = list.filter(function (p) { return p.field === f.slug; });
          if (ps.length) groups.push({ field: f, people: ps });
        });
        var other = list.filter(function (p) { return !fields[p.field]; });
        if (other.length) groups.push({ field: null, people: other });
      }

      // Show names on the lines when they fit in a reasonable height,
      // otherwise draw compact lines and reveal names on hover.
      var labelled = true;
      var packed = groups.map(function (g) { return pack(g.people, true); });
      var laneH = 34;
      var total = packed.reduce(function (s, r) { return s + r.lanes; }, 0);
      var maxH = Math.max(300, Math.min(window.innerHeight * 0.52, 520));
      if (total * laneH + groups.length * 22 > maxH) {
        labelled = false;
        laneH = 15;
        packed = groups.map(function (g) { return pack(g.people, false); });
      }
      canvas.classList.toggle("is-compact", !labelled);

      var y = 10;
      var bandGap = labelled ? 14 : 20;
      bandLayer.innerHTML = "";
      geo = {};
      groups.forEach(function (g, gi) {
        var r = packed[gi];
        var top = y;
        if (groups.length > 1) {
          var band = h("div", "aoc-tl-band");
          band.style.top = top + "px";
          band.style.height = r.lanes * laneH + "px";
          if (g.field) band.style.setProperty("--c", g.field.color);
          band.appendChild(h("span", "aoc-tl-band-label", g.field ? g.field.name : ""));
          bandLayer.appendChild(band);
        }
        r.placed.forEach(function (it) {
          var barY = top + it.lane * laneH + (labelled ? 21 : 5);
          geo[it.p.id] = { x0: it.x0, x1: it.x1, y: barY };
        });
        y += r.lanes * laneH + bandGap;
      });
      var height = y - bandGap + 12;
      plot.style.height = height + "px";
      links.setAttribute("width", width);
      links.setAttribute("height", height);
      links.setAttribute("viewBox", "0 0 " + width + " " + height);

      var i = 0;
      data.people.forEach(function (p) {
        var b = nodes[p.id];
        var g = geo[p.id];
        if (!g) {
          b.classList.add("is-out");
          b.tabIndex = -1;
          return;
        }
        b.classList.remove("is-out");
        b.tabIndex = 0;
        b.style.left = g.x0 + "px";
        b.style.top = (g.y - (labelled ? 21 : 5)) + "px";
        b.style.width = Math.max(g.x1 - g.x0, 4) + "px";
        b.classList.toggle("is-cut", p.died == null || p.died > data.end);
        b.style.setProperty("--i", i++);
      });

      drawAxis();
      drawEras();
      paint();
    }

    function drawAxis() {
      axis.innerHTML = "";
      var step = 50;
      var first = Math.ceil(data.start / step) * step;
      for (var yr = first; yr <= data.end; yr += step) {
        var major = yr % 100 === 0;
        var t = h("span", "aoc-tl-tick" + (major ? " is-major" : ""));
        t.style.left = x(yr) + "px";
        if (major || width > 1200) t.appendChild(h("span", null, String(yr)));
        axis.appendChild(t);
      }
      // Faint century gridlines through the plot.
      plot.style.setProperty("--grid-start", x(first) + "px");
      plot.style.setProperty("--grid-step", (x(first + 100) - x(first)) + "px");
    }

    function drawEras() {
      eras.innerHTML = "";
      var f = state.field ? fields[state.field] : null;
      var list = f ? f.eras : data.periods;
      eras.classList.toggle("is-field", !!f);
      if (f) eras.style.setProperty("--c", f.color);
      (list || []).forEach(function (e) {
        var a = Math.max(e.start, data.start);
        var b = Math.min(e.end, data.end);
        if (b <= a) return;
        var el = h("div", "aoc-tl-era");
        el.style.left = x(a) + "px";
        el.style.width = (x(b) - x(a)) + "px";
        el.title = e.label + " (" + e.start + "–" + e.end + ")";
        el.appendChild(h("span", null, e.label));
        eras.appendChild(el);
      });
    }

    /* ---------- state painting ---------- */
    function paint() {
      var active = state.hover != null ? state.hover : state.selected;
      var linked = {};
      if (active != null && byId[active]) {
        byId[active].links.forEach(function (id) { linked[id] = true; });
      }
      var anyMatch = false;
      data.people.forEach(function (p) {
        var b = nodes[p.id];
        var m = matches(p);
        if (m && state.query && geo[p.id]) anyMatch = true;
        b.classList.toggle("is-active", p.id === active);
        b.classList.toggle("is-selected", p.id === state.selected);
        b.classList.toggle("is-linked", !!linked[p.id]);
        b.classList.toggle("is-match", !!state.query && m);
        b.classList.toggle("is-dim",
          (active != null && p.id !== active && !linked[p.id]) ||
          (active == null && !!state.query && !m));
        b.setAttribute("aria-pressed", p.id === state.selected ? "true" : "false");
      });
      root.classList.toggle("has-no-match", !!state.query && !anyMatch);
      drawLinks(active);
      drawTip(state.hover);
    }

    function drawLinks(active) {
      while (links.firstChild) links.removeChild(links.firstChild);
      if (active == null || !geo[active]) return;
      var a = geo[active];
      var p = byId[active];
      var color = fields[p.field] ? fields[p.field].color : "#6b665e";
      p.links.forEach(function (id) {
        var b = geo[id];
        if (!b) return;
        var x1 = (a.x0 + a.x1) / 2, y1 = a.y;
        var x2 = (b.x0 + b.x1) / 2, y2 = b.y;
        var lift = Math.min(90, 24 + Math.abs(x2 - x1) * 0.18);
        var top = Math.min(y1, y2) - lift;
        var path = document.createElementNS(SVG_NS, "path");
        path.setAttribute("d", "M" + x1 + "," + y1 + " C" + x1 + "," + top + " " + x2 + "," + top + " " + x2 + "," + y2);
        path.setAttribute("stroke", color);
        links.appendChild(path);
        [[x1, y1], [x2, y2]].forEach(function (pt) {
          var c = document.createElementNS(SVG_NS, "circle");
          c.setAttribute("cx", pt[0]);
          c.setAttribute("cy", pt[1]);
          c.setAttribute("r", 3);
          c.setAttribute("fill", color);
          links.appendChild(c);
        });
      });
    }

    function drawTip(id) {
      if (id == null || !geo[id] || id === state.selected) {
        tip.classList.remove("is-on");
        return;
      }
      var p = byId[id];
      var g = geo[id];
      var f = fields[p.field];
      tip.innerHTML = "";
      tip.appendChild(h("strong", null, p.name));
      tip.appendChild(h("span", "aoc-tl-tip-meta", years(p) + (f ? " · " + f.name : "")));
      if (p.description) tip.appendChild(h("span", "aoc-tl-tip-desc", p.description));
      var cx = (g.x0 + g.x1) / 2;
      cx = Math.max(150, Math.min(width - 150, cx));
      tip.style.left = cx + "px";
      // Above the line if there's room inside the stage, otherwise below it.
      var room = g.y + plot.offsetTop + canvas.offsetTop;
      var below = tip.offsetHeight + 24 > room;
      tip.classList.toggle("is-below", below);
      tip.style.top = (below ? g.y + 14 : g.y - 16) + "px";
      tip.classList.add("is-on");
    }

    /* ---------- detail card ---------- */
    function renderDetail() {
      detail.innerHTML = "";
      var p = state.selected != null ? byId[state.selected] : null;
      if (!p) {
        if (hintText) detail.appendChild(h("p", "aoc-tl-hint", hintText));
        return;
      }
      var f = fields[p.field];
      var card = h("article", "aoc-tl-card");
      card.style.setProperty("--c", f ? f.color : "#6b665e");
      var meta = h("p", "aoc-tl-card-meta");
      if (f) meta.appendChild(h("span", "aoc-tl-card-field", f.name));
      var life = years(p);
      if (p.died != null) life += " · " + (p.died - p.born) + " years";
      meta.appendChild(h("span", null, life));
      card.appendChild(meta);
      card.appendChild(h("h2", "aoc-tl-card-name", p.name));
      if (p.description) card.appendChild(h("p", "aoc-tl-card-desc", p.description));
      if (p.links.length) {
        var row = h("div", "aoc-tl-card-links");
        row.appendChild(h("span", "aoc-tl-card-label", "Connected to"));
        p.links
          .map(function (id) { return byId[id]; })
          .sort(function (a, b) { return a.born - b.born; })
          .forEach(function (q) {
            var chip = h("button", "aoc-tl-chip", q.name);
            chip.type = "button";
            chip.style.setProperty("--c", fields[q.field] ? fields[q.field].color : "#6b665e");
            chip.addEventListener("click", function () { select(q.id, true); });
            row.appendChild(chip);
          });
        card.appendChild(row);
      }
      var close = h("button", "aoc-tl-close");
      close.type = "button";
      close.setAttribute("aria-label", "Close");
      close.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
      close.addEventListener("click", function () { select(null); });
      card.appendChild(close);
      detail.appendChild(card);
    }

    /* ---------- actions ---------- */
    function setHover(id) {
      state.hover = id;
      paint();
    }

    function select(id, reveal) {
      state.selected = id;
      if (id != null && state.field && byId[id].field !== state.field) {
        setField("");
      }
      renderDetail();
      paint();
      if (id != null && reveal) {
        var b = nodes[id];
        b.focus({ preventScroll: true });
        scrollIntoStage(id);
      }
    }

    function scrollIntoStage(id) {
      var g = geo[id];
      if (!g) return;
      var mid = (g.x0 + g.x1) / 2;
      if (mid < stage.scrollLeft + 40 || mid > stage.scrollLeft + stage.clientWidth - 40) {
        stage.scrollTo({ left: mid - stage.clientWidth / 2, behavior: reduceMotion ? "auto" : "smooth" });
      }
    }

    function setField(slug) {
      state.field = slug;
      fieldButtons.forEach(function (b) {
        b.setAttribute("aria-pressed", b.getAttribute("data-field") === slug ? "true" : "false");
      });
      root.style.setProperty("--aoc-active-field", slug && fields[slug] ? fields[slug].color : "");
      layout();
    }

    fieldButtons.forEach(function (b) {
      b.addEventListener("click", function () {
        var slug = b.getAttribute("data-field");
        setField(state.field === slug ? "" : slug);
      });
    });

    if (search) {
      search.addEventListener("input", function () {
        state.query = search.value.trim();
        paint();
      });
      search.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
          e.preventDefault();
          var hit = data.people.filter(function (p) { return state.query && matches(p); });
          if (hit.length) select(hit[0].id, true);
        }
      });
    }

    root.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        if (state.selected != null) select(null);
        else if (search && search.value) { search.value = ""; state.query = ""; paint(); }
      }
    });

    /* ---------- go ---------- */
    layout();
    renderDetail();
    if (!reduceMotion) {
      root.classList.add("is-entering");
      requestAnimationFrame(function () {
        requestAnimationFrame(function () { root.classList.add("is-in"); });
      });
      setTimeout(function () { root.classList.remove("is-entering"); }, 2200);
    } else {
      root.classList.add("is-in");
    }

    var lastW = stage.clientWidth;
    if (window.ResizeObserver) {
      new ResizeObserver(function () {
        if (stage.clientWidth !== lastW) {
          lastW = stage.clientWidth;
          layout();
        }
      }).observe(stage);
    }
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(layout);
  }

  function boot() {
    document.querySelectorAll("[data-aoc-timeline]").forEach(init);
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot);
  else boot();
})();
