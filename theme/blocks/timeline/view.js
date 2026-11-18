/* Creators timeline: turns the server-rendered list into an interactive
   chart. Each creator is a line from birth to death, grouped by field.
   Point at (or focus) a line to see the person and their connections;
   select it to pin a card with the details. No dependencies. */
(function () {
  "use strict";

  var SVG_NS = "http://www.w3.org/2000/svg";
  var MIN_WIDTH = 920;
  var GUTTER = 128; // room for field names left of the plot
  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var uid = 0;

  function h(tag, cls, text) {
    var el = document.createElement(tag);
    if (cls) el.className = cls;
    if (text != null) el.textContent = text;
    return el;
  }

  function svg(tag, attrs) {
    var el = document.createElementNS(SVG_NS, tag);
    for (var k in attrs) el.setAttribute(k, attrs[k]);
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
    var id = "aoc-tl-" + (++uid);

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
      (p.connections || []).forEach(function (cid) {
        var q = byId[cid];
        if (!q || q === p) return;
        if (p.links.indexOf(q.id) === -1) p.links.push(q.id);
        if (q.links.indexOf(p.id) === -1) q.links.push(p.id);
      });
    });
    function colorOf(p) {
      return fields[p.field] ? fields[p.field].color : "#8a857b";
    }

    var state = { field: "", query: "", hover: null, selected: null };

    /* ---------- build DOM ---------- */
    canvas.innerHTML = "";
    canvas.classList.add("is-ready");
    var plot = h("div", "aoc-tl-plot");
    var bandLayer = h("div", "aoc-tl-bands");
    bandLayer.setAttribute("aria-hidden", "true");
    var links = svg("svg", { "class": "aoc-tl-links", "aria-hidden": "true" });
    var defs = svg("defs", {});
    links.appendChild(defs);
    var linkLayer = svg("g", {});
    links.appendChild(linkLayer);
    var tags = h("div", "aoc-tl-tags");
    tags.setAttribute("aria-hidden", "true");
    plot.appendChild(bandLayer);
    plot.appendChild(links);
    var axis = h("div", "aoc-tl-axis");
    axis.setAttribute("aria-hidden", "true");
    var eras = h("div", "aoc-tl-eras");
    // Hover read-out: a quiet caption line above the chart instead of a
    // floating box, so nothing covers the lines being looked at.
    var tip = h("div", "aoc-tl-readout");
    tip.setAttribute("aria-hidden", "true");
    root.insertBefore(tip, stage);
    canvas.appendChild(plot);
    canvas.appendChild(axis);
    canvas.appendChild(eras);

    var nodes = {};
    data.people.forEach(function (p) {
      var b = h("button", "aoc-tl-life");
      b.type = "button";
      var f = fields[p.field];
      b.style.setProperty("--c", colorOf(p));
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
    plot.appendChild(tags);

    /* ---------- helpers ---------- */
    function years(p) {
      return p.born + "–" + (p.died == null ? "" : p.died);
    }

    var measureCtx = document.createElement("canvas").getContext("2d");
    function textWidth(s, px) {
      measureCtx.font = "500 " + (px || 12.5) + "px " + (getComputedStyle(root).getPropertyValue("--aoc-ui-font") || "sans-serif");
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
    var padL = GUTTER;
    var padR = 28;
    var labelled = false;

    function x(year) {
      var t = (year - data.start) / (data.end - data.start);
      return padL + t * (width - padL - padR);
    }

    // Greedy interval packing: each person goes in the first lane where
    // they don't overlap anyone (including the space their name needs).
    function pack(list, withNames) {
      var lanes = [];
      var placed = [];
      list.slice().sort(function (a, b) { return a.born - b.born; }).forEach(function (p) {
        var x0 = Math.max(x(p.born), x(data.start));
        var x1 = Math.min(x(p.died == null ? data.end : p.died), x(data.end));
        var reach = withNames ? Math.max(x1, x0 + textWidth(p.name) + 6) : x1;
        var lane = 0;
        while (lane < lanes.length && lanes[lane] > x0 - (withNames ? 16 : 8)) lane++;
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
      padL = groups.length > 1 ? GUTTER : 28;

      // Show names on the lines when they fit in a reasonable height,
      // otherwise draw compact lines and reveal names on hover.
      labelled = true;
      var laneH = 32;
      var packed = groups.map(function (g) { return pack(g.people, true); });
      var total = packed.reduce(function (s, r) { return s + r.lanes; }, 0);
      var maxH = Math.max(300, Math.min(window.innerHeight * 0.52, 520));
      if (total * laneH + groups.length * 22 > maxH) {
        labelled = false;
        laneH = 13;
        packed = groups.map(function (g) { return pack(g.people, false); });
      }
      canvas.classList.toggle("is-compact", !labelled);
      canvas.classList.toggle("has-bands", groups.length > 1);

      var y = 14;
      var bandGap = labelled ? 12 : 22;
      var barOffset = labelled ? 22 : 6;
      bandLayer.innerHTML = "";
      geo = {};
      groups.forEach(function (g, gi) {
        var r = packed[gi];
        var top = y;
        if (groups.length > 1) {
          var band = h("div", "aoc-tl-band");
          band.style.top = (top - 4) + "px";
          band.style.height = (r.lanes * laneH + 8) + "px";
          band.style.left = (padL - 16) + "px";
          if (g.field) band.style.setProperty("--c", g.field.color);
          var lab = h("span", "aoc-tl-band-label");
          lab.style.width = (padL - 28) + "px";
          lab.style.left = -(padL - 16) + "px";
          lab.appendChild(h("span", "aoc-tl-band-dot"));
          lab.appendChild(document.createTextNode(g.field ? g.field.name : ""));
          var n = h("span", "aoc-tl-band-count", String(g.people.length));
          lab.appendChild(n);
          band.appendChild(lab);
          bandLayer.appendChild(band);
        }
        r.placed.forEach(function (it) {
          geo[it.p.id] = { x0: it.x0, x1: it.x1, y: top + it.lane * laneH + barOffset };
        });
        y += r.lanes * laneH + bandGap;
      });
      var height = y - bandGap + 14;
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
        b.style.top = (g.y - barOffset) + "px";
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
      axis.style.setProperty("--axis-start", x(data.start) + "px");
      axis.style.setProperty("--axis-end", (width - x(data.end)) + "px");
      // Faint century gridlines through the plot.
      var firstCentury = Math.ceil(data.start / 100) * 100;
      plot.style.setProperty("--grid-start", x(firstCentury) + "px");
      plot.style.setProperty("--grid-step", (x(firstCentury + 100) - x(firstCentury)) + "px");
      plot.style.setProperty("--grid-end", (width - x(data.end)) + "px");
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
        byId[active].links.forEach(function (lid) { linked[lid] = true; });
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
      root.classList.toggle("has-active", active != null);
      root.classList.toggle("has-no-match", !!state.query && !anyMatch);
      var tipBox = drawTip(state.hover);
      drawLinks(active);
      drawTags(active, tipBox);
    }

    function mid(g) {
      return (g.x0 + g.x1) / 2;
    }

    // Arcs from the person to each connection: a thin curve that bows
    // upward, fading from the person's field colour to the other's.
    function drawLinks(active) {
      while (linkLayer.firstChild) linkLayer.removeChild(linkLayer.firstChild);
      while (defs.firstChild) defs.removeChild(defs.firstChild);
      if (active == null || !geo[active]) return;
      var a = geo[active];
      var p = byId[active];
      var x1 = mid(a), y1 = a.y;
      p.links.forEach(function (lid, n) {
        var b = geo[lid];
        if (!b) return;
        var q = byId[lid];
        var x2 = mid(b), y2 = b.y;
        var dx = Math.abs(x2 - x1);
        var lift = Math.max(22, Math.min(96, dx * 0.32));
        var top = Math.min(y1, y2) - lift;
        var gid = id + "-g" + n;
        var grad = svg("linearGradient", { id: gid, gradientUnits: "userSpaceOnUse", x1: x1, y1: 0, x2: x2, y2: 0 });
        grad.appendChild(svg("stop", { offset: "0", "stop-color": colorOf(p) }));
        grad.appendChild(svg("stop", { offset: "1", "stop-color": colorOf(q) }));
        defs.appendChild(grad);
        var path = svg("path", {
          d: "M" + x1 + "," + y1 + " C" + x1 + "," + top + " " + x2 + "," + top + " " + x2 + "," + y2,
          stroke: "url(#" + gid + ")",
          "class": "aoc-tl-arc"
        });
        linkLayer.appendChild(path);
        if (!reduceMotion && path.getTotalLength) {
          var len = path.getTotalLength();
          path.style.strokeDasharray = len;
          path.style.strokeDashoffset = len;
          path.getBoundingClientRect();
          path.style.transition = "stroke-dashoffset 0.45s cubic-bezier(0.25, 1, 0.5, 1) " + (n * 0.03) + "s";
          path.style.strokeDashoffset = "0";
        }
        linkLayer.appendChild(svg("circle", { cx: x2, cy: y2, r: 3.2, fill: colorOf(q), "class": "aoc-tl-node" }));
      });
      linkLayer.appendChild(svg("circle", { cx: x1, cy: y1, r: 4, fill: colorOf(p), "class": "aoc-tl-node is-source" }));
    }

    // In the compact view, put small name tags on connected people, but
    // only where they don't collide with each other or the tooltip.
    function drawTags(active, avoid) {
      tags.innerHTML = "";
      if (labelled || active == null || !geo[active]) return;
      // (in the labelled view the names are already on the lines)
      var boxes = avoid ? [avoid] : [];
      function free(r) {
        return boxes.every(function (o) {
          return r.x1 < o.x0 - 4 || r.x0 > o.x1 + 4 || r.y1 < o.y0 - 2 || r.y0 > o.y1 + 2;
        });
      }
      var list = [active].concat(byId[active].links).filter(function (lid) { return geo[lid]; });
      list.forEach(function (lid, n) {
        var g = geo[lid];
        var p = byId[lid];
        var w = textWidth(p.name, 11.5) + 12;
        var cx = Math.max(w / 2 + 2, Math.min(width - w / 2 - 2, mid(g)));
        var r = { x0: cx - w / 2, x1: cx + w / 2, y0: g.y - 24, y1: g.y - 6 };
        if (n > 0 && !free(r)) return;
        boxes.push(r);
        var t = h("span", "aoc-tl-tag" + (n === 0 ? " is-source" : ""), p.name);
        t.style.left = r.x0 + "px";
        t.style.top = r.y0 + "px";
        t.style.setProperty("--c", colorOf(p));
        tags.appendChild(t);
      });
    }

    function drawTip(tid) {
      var show = tid;
      if (show == null || !byId[show] || show === state.selected) {
        tip.classList.remove("is-on");
        return null;
      }
      var p = byId[show];
      var f = fields[p.field];
      tip.innerHTML = "";
      tip.style.setProperty("--c", colorOf(p));
      tip.appendChild(h("span", "aoc-tl-readout-dot"));
      tip.appendChild(h("strong", "aoc-tl-readout-name", p.name));
      tip.appendChild(h("span", "aoc-tl-readout-meta", years(p) + (f ? " · " + f.name : "")));
      if (p.description) tip.appendChild(h("span", "aoc-tl-readout-desc", p.description));
      tip.classList.add("is-on");
      return null;
    }

    /* ---------- detail card ---------- */
    function renderDetail() {
      detail.innerHTML = "";
      var p = state.selected != null ? byId[state.selected] : null;
      if (!p) {
        detail.appendChild(legend());
        return;
      }
      var f = fields[p.field];
      var card = h("article", "aoc-tl-card");
      card.style.setProperty("--c", colorOf(p));
      var meta = h("p", "aoc-tl-card-meta");
      if (f) meta.appendChild(h("span", "aoc-tl-card-field", f.name));
      meta.appendChild(h("span", null, years(p)));
      if (p.died != null) meta.appendChild(h("span", null, "Lived " + (p.died - p.born) + " years"));
      card.appendChild(meta);
      card.appendChild(h("h2", "aoc-tl-card-name", p.name));
      if (p.description) card.appendChild(h("p", "aoc-tl-card-desc", p.description));
      if (p.links.length) {
        var row = h("div", "aoc-tl-card-links");
        row.appendChild(h("span", "aoc-tl-card-label", "Connected to"));
        var chips = h("div", "aoc-tl-chips");
        p.links
          .map(function (lid) { return byId[lid]; })
          .sort(function (a, b) { return a.born - b.born; })
          .forEach(function (q) {
            var chip = h("button", "aoc-tl-chip");
            chip.type = "button";
            chip.style.setProperty("--c", colorOf(q));
            chip.appendChild(h("span", "aoc-tl-chip-name", q.name));
            chip.appendChild(h("span", "aoc-tl-chip-years", years(q)));
            chip.addEventListener("click", function () { select(q.id, true); });
            chips.appendChild(chip);
          });
        row.appendChild(chips);
        card.appendChild(row);
      }
      var close = h("button", "aoc-tl-close");
      close.type = "button";
      close.setAttribute("aria-label", "Close");
      close.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
      close.addEventListener("click", function () { select(null); });
      card.appendChild(close);
      detail.appendChild(card);
    }

    // Key explaining the two marks, plus the editor's hint text.
    function legend() {
      var k = h("div", "aoc-tl-legend");
      var life = h("span", "aoc-tl-key");
      life.innerHTML = '<svg viewBox="0 0 34 10" width="34" height="10" aria-hidden="true"><rect x="1" y="3.5" width="32" height="3" rx="1.5" fill="currentColor"/></svg>';
      life.appendChild(document.createTextNode("A life, from birth to death"));
      var link = h("span", "aoc-tl-key");
      link.innerHTML = '<svg viewBox="0 0 34 14" width="34" height="14" aria-hidden="true"><path d="M3 12 C3 1 31 1 31 12" fill="none" stroke="currentColor" stroke-width="1.3"/><circle cx="3" cy="12" r="2" fill="currentColor"/><circle cx="31" cy="12" r="2" fill="currentColor"/></svg>';
      link.appendChild(document.createTextNode("Connection: influence, teaching or exchange"));
      k.appendChild(life);
      k.appendChild(link);
      if (hintText) k.appendChild(h("span", "aoc-tl-key aoc-tl-key-hint", hintText));
      return k;
    }

    /* ---------- actions ---------- */
    function setHover(hid) {
      state.hover = hid;
      paint();
    }

    function select(sid, reveal) {
      state.selected = sid;
      if (sid != null && state.field && byId[sid].field !== state.field) {
        setField("");
      }
      renderDetail();
      paint();
      if (sid != null && reveal) {
        nodes[sid].focus({ preventScroll: true });
        scrollIntoStage(sid);
      }
    }

    function scrollIntoStage(sid) {
      var g = geo[sid];
      if (!g) return;
      var m = mid(g);
      if (m < stage.scrollLeft + 40 || m > stage.scrollLeft + stage.clientWidth - 40) {
        stage.scrollTo({ left: m - stage.clientWidth / 2, behavior: reduceMotion ? "auto" : "smooth" });
      }
    }

    function setField(slug) {
      state.field = slug;
      fieldButtons.forEach(function (b) {
        b.setAttribute("aria-pressed", b.getAttribute("data-field") === slug ? "true" : "false");
      });
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
