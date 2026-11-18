(function () {
  "use strict";

  var MAX_SPEED = 4.2;
  var RESTITUTION = 0.9;

  function clamp(v, lo, hi) {
    return Math.max(lo, Math.min(hi, v));
  }

  // Small seeded RNG (mulberry32) so each person's look is stable across reloads.
  function seedFrom(id) {
    var h = 1779033703 ^ id.length;
    for (var i = 0; i < id.length; i++) {
      h = Math.imul(h ^ id.charCodeAt(i), 3432918353);
      h = (h << 13) | (h >>> 19);
    }
    return function () {
      h = Math.imul(h ^ (h >>> 16), 2246822507);
      h = Math.imul(h ^ (h >>> 13), 3266489909);
      h ^= h >>> 16;
      return (h >>> 0) / 4294967296;
    };
  }

  var paramCache = {};
  var SHARED_HUE = 200; // all bubbles share one colour; only glints/shape vary per bubble

  function paramsFor(id) {
    if (paramCache[id]) return paramCache[id];
    var rnd = seedFrom(id);
    rnd(); // burn the draw that used to pick hue, so downstream values stay identical in spirit
    var hue = SHARED_HUE;
    var hasSecond = rnd() > 0.4;
    var arcAngle = rnd() * Math.PI * 2;
    rnd(); // burn the draw that used to vary hue2's offset, keep hue2 fixed relative to hue instead
    var p = {
      hue: hue,
      hue2: (hue + 150) % 360,
      hlX: 22 + rnd() * 30,
      hlY: 16 + rnd() * 28,
      hlSize: 7 + rnd() * 9,
      hlA: 0.75 + rnd() * 0.23,
      hl2X: 55 + rnd() * 25,
      hl2Y: 58 + rnd() * 26,
      hl2Size: 3 + rnd() * 5,
      hl2A: hasSecond ? 0.3 + rnd() * 0.35 : 0,
      arcX: 50 + Math.cos(arcAngle) * (24 + rnd() * 8),
      arcY: 50 + Math.sin(arcAngle) * (24 + rnd() * 8),
      arcA: 0.3 + rnd() * 0.3,
      tintA: 0.16 + rnd() * 0.1,
      rimA: 0.5 + rnd() * 0.24,
      blur: 0.5 + rnd() * 1.2
    };
    paramCache[id] = p;
    return p;
  }


  function applyBubbleStyle(el, p, active) {
    // Pearl sphere with richer shading: crisp specular highlight, a soft
    // secondary glint, a thin saturated accent rim (same shared hue as the
    // body, just punchier), and deeper volume shadow for a glossier feel.
    el.style.background = [
      "radial-gradient(circle at " + p.hlX + "% " + p.hlY + "%, rgba(255,255,255," + p.hlA + ") 0%, rgba(255,255,255," + (p.hlA * 0.5) + ") " + (p.hlSize * 0.6) + "%, rgba(255,255,255,0) " + p.hlSize + "%)",
      "radial-gradient(circle at " + p.hl2X + "% " + p.hl2Y + "%, rgba(255,255,255," + p.hl2A + ") 0%, rgba(255,255,255,0) " + p.hl2Size + "%)",
      "radial-gradient(circle at 50% 50%, rgba(255,255,255,0) 84%, hsla(" + p.hue + ", 65%, 62%, 0.5) 92%, hsla(" + p.hue + ", 60%, 50%, 0.28) 100%)",
      "radial-gradient(circle at 32% 28%, #ffffff 0%, #f4f9fc 30%, #dcebf5 60%, #b9d3e6 100%)"
    ].join(", ");
    el.style.border = "1px solid hsla(" + p.hue + ", 55%, 65%, 0.55)";
    el.style.boxShadow = [
      "inset 0 -14px 20px hsla(" + p.hue + ", 45%, 45%, 0.32)",
      "inset 0 8px 12px rgba(255, 255, 255, 0.9)",
      active ? "0 18px 34px hsla(" + p.hue + ", 55%, 40%, 0.34)" : "0 10px 22px hsla(" + p.hue + ", 40%, 35%, 0.2)"
    ].join(", ");
    el.style.backdropFilter = "none";
    el.style.webkitBackdropFilter = "none";
  }

  function init() {
    var field = document.getElementById("aoc-bubble-field");
    if (!field || !Array.isArray(window.aocBubbleData)) return;

    var people = window.aocBubbleData;

    // Size scales off works^EXPONENT rather than a plain sqrt — a smaller
    // exponent flattens the curve further (less linear-feeling: small
    // composers don't look tiny, big ones don't dominate), and mapping
    // against this dataset's actual min/max means nobody gets clipped by
    // a hardcoded cap the way several composers used to collide at 98px.
    var EXPONENT = 0.3;
    var MIN_R = 70, MAX_R = 104;
    var worksValues = people.map(function (p) { return Math.max(1, p.works || 1); });
    var minScaled = Math.pow(Math.min.apply(null, worksValues), EXPONENT);
    var maxScaled = Math.pow(Math.max.apply(null, worksValues), EXPONENT);
    var scaledSpan = maxScaled - minScaled || 1;

    function radiusFor(works) {
      var v = Math.pow(Math.max(1, works || 1), EXPONENT);
      var t = (v - minScaled) / scaledSpan;
      return MIN_R + t * (MAX_R - MIN_R);
    }

    field.classList.add("aoc-bubble-field");
    field.setAttribute("aria-label", "Interactive field of researchers and composers");

    var canvas = document.createElement("canvas");
    canvas.className = "aoc-bubble-canvas";
    canvas.setAttribute("aria-hidden", "true");
    field.appendChild(canvas);
    var ctx = canvas.getContext("2d");

    var W = 0, H = 0, dpr = 1;
    var bodies = [];
    var ripples = [];
    var dragId = null, dragOffX = 0, dragOffY = 0, moved = false;

    function sizeCanvas() {
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(W * dpr);
      canvas.height = Math.round(H * dpr);
      canvas.style.width = W + "px";
      canvas.style.height = H + "px";
    }

    function spawnRipple(x, y, hue, strength) {
      if (ripples.length > 60) return;
      ripples.push({ x: x, y: y, r: 4, maxR: 24 + strength * 26, life: 1, hue: hue });
    }

    function build() {
      var rect = field.getBoundingClientRect();
      W = rect.width;
      H = rect.height || 520;
      if (W < 2 || H < 2) { requestAnimationFrame(build); return; }
      sizeCanvas();

      // Keep spawn positions out of the hero/footer obstacles up front —
      // otherwise a bubble spawning inside one gets instantly teleported to
      // the nearest edge on the very first physics frame, which reads as a
      // jarring "flick" right as the page loads.
      var heroRect = heroObstacleRect();
      var footerRect = footerObstacleRect();
      var headerRect = headerObstacleRect();

      bodies = people.map(function (person, i) {
        var r = radiusFor(person.works);
        var cols = Math.ceil(Math.sqrt(people.length));
        var cellW = W / cols;
        var cellH = H / Math.ceil(people.length / cols);
        var col = i % cols;
        var row = Math.floor(i / cols);
        var x = clamp(cellW * col + cellW / 2 + (Math.random() - 0.5) * 24, r, W - r);
        var y = clamp(cellH * row + cellH / 2 + (Math.random() - 0.5) * 24, r, H - r);

        var withinX = function (obs) { return x + r > obs.left && x - r < obs.right; };
        if (heroRect && withinX(heroRect) && y - r < heroRect.bottom) {
          y = clamp(heroRect.bottom + r + 6, r, H - r);
        }
        if (footerRect && withinX(footerRect) && y + r > footerRect.top) {
          y = clamp(footerRect.top - r - 6, r, H - r);
        }
        if (headerRect && withinX(headerRect) && y - r < headerRect.bottom) {
          y = clamp(headerRect.bottom + r + 6, r, H - r);
        }

        var angle = Math.random() * Math.PI * 2;
        var speed = 0.5 + Math.random() * 0.7;

        var el = field.querySelector('[data-id="' + person.id + '"]') || document.createElement("button");
        el.type = "button";
        el.className = "aoc-bubble";
        el.setAttribute("data-id", person.id);
        el.style.width = el.style.height = (r * 2) + "px";
        el.innerHTML =
          '<span class="aoc-bubble-name">' + person.name + "</span>" +
          (person.years ? '<span class="aoc-bubble-years">' + person.years + "</span>" : "") +
          (person.works ? '<span class="aoc-bubble-works">' + person.works + " works</span>" : "");
        applyBubbleStyle(el, paramsFor(person.id), false);
        if (!el.isConnected) field.appendChild(el);

        el.onpointerdown = function (e) {
          moved = false;
          dragId = person.id;
          body.vx = 0;
          body.vy = 0;
          var r2 = field.getBoundingClientRect();
          dragOffX = e.clientX - r2.left - body.x;
          dragOffY = e.clientY - r2.top - body.y;
          body.dragging = true;
        };
        el.onclick = function () {
          if (moved) { moved = false; return; }
          if (person.url) window.open(person.url, "_blank", "noopener");
        };

        var body = {
          person: person,
          el: el,
          x: x, y: y, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed,
          r: r, mass: r * r,
          phase: Math.random() * Math.PI * 2,
          squash: 0,
          dragging: false
        };
        return body;
      });
    }

    // The hero title acts as a solid obstacle bubbles bounce off of. Its
    // position is recomputed every frame since it scrolls with the page
    // while the bubble field itself stays fixed to the viewport. The
    // obstacle's top is clamped to 0 (the field's own top wall) rather
    // than the title's actual top edge — otherwise there's a narrow gap
    // between the top wall and the title (behind the opaque header, which
    // isn't itself a collision obstacle) that's too small for large
    // bubbles to fit through, and they get stuck jittering in it.
    function heroObstacleRect() {
      var el = document.querySelector(".aoc-hero-text");
      if (!el) return null;
      var r = el.getBoundingClientRect();
      var fieldRect = field.getBoundingClientRect();
      var pad = 14;
      return {
        left: r.left - fieldRect.left - pad,
        right: r.right - fieldRect.left + pad,
        top: 0,
        bottom: r.bottom - fieldRect.top + pad
      };
    }

    // The header bar itself is a solid obstacle too, pinned to the field's
    // TOP wall (same reasoning as heroObstacleRect's top clamp) so bubbles
    // bounce off its visible bottom edge instead of drifting underneath it
    // while it's still on screen. It scrolls away with the page like any
    // other in-flow element, so once its bottom edge passes above the
    // viewport this rect simply stops intersecting anything.
    function headerObstacleRect() {
      // Target the wp-site-blocks direct-child <header> wrapper rather than
      // .aoc-header: the header template part is editable in the block
      // editor, and once it's been customized there its saved markup no
      // longer necessarily carries the aoc-header class (WordPress renders
      // whatever's stored in the wp_template_part post, not the theme
      // file's original markup) — .aoc-header returning null here silently
      // disabled the obstacle entirely.
      var el = document.querySelector(".wp-site-blocks > header");
      if (!el) return null;
      var r = el.getBoundingClientRect();
      var fieldRect = field.getBoundingClientRect();
      var pad = 14;
      return {
        left: r.left - fieldRect.left - pad,
        right: r.right - fieldRect.left + pad,
        top: 0,
        bottom: r.bottom - fieldRect.top + pad
      };
    }

    // Same idea as heroObstacleRect, but pinned to the field's BOTTOM wall
    // instead of the top — the footer sits flush against the bottom of the
    // viewport, so its bottom edge is clamped to H rather than its actual
    // (usually off-screen-ish) bottom edge.
    function footerObstacleRect() {
      var el = document.querySelector(".wp-site-blocks > footer");
      if (!el) return null;
      var r = el.getBoundingClientRect();
      var fieldRect = field.getBoundingClientRect();
      var pad = 14;
      return {
        left: r.left - fieldRect.left - pad,
        right: r.right - fieldRect.left + pad,
        top: r.top - fieldRect.top - pad,
        bottom: H
      };
    }

    function resolveRectCollision(b, rect) {
      if (!rect) return;

      var insideX = b.x > rect.left && b.x < rect.right;
      var insideY = b.y > rect.top && b.y < rect.bottom;

      if (insideX && insideY) {
        // Bubble's center is fully inside the obstacle — happens at initial
        // spawn (grid positions aren't obstacle-aware) and after a drag
        // release. There's no single "nearest edge" via distance-to-surface
        // here since the center itself is inside. Escape through whichever
        // side is nearest, but only consider sides that aren't themselves
        // walled off by the field's own boundary (e.g. the hero's top edge
        // sits at the top wall, and the footer's bottom edge sits at the
        // bottom wall — pushing "through" those used to make bubbles
        // oscillate in place forever).
        var candidates = [
          { side: "left", dist: b.x - rect.left },
          { side: "right", dist: rect.right - b.x }
        ];
        if (rect.top > 0.5) candidates.push({ side: "top", dist: b.y - rect.top });
        if (rect.bottom < H - 0.5) candidates.push({ side: "bottom", dist: rect.bottom - b.y });
        candidates.sort(function (a, c) { return a.dist - c.dist; });
        var pick = candidates[0];

        if (pick.side === "bottom") {
          b.y = rect.bottom + b.r;
          b.vy = Math.abs(b.vy) < 0.4 ? 0.6 : Math.abs(b.vy);
        } else if (pick.side === "top") {
          b.y = rect.top - b.r;
          b.vy = Math.abs(b.vy) < 0.4 ? -0.6 : -Math.abs(b.vy);
        } else if (pick.side === "left") {
          b.x = rect.left - b.r;
          b.vx = Math.abs(b.vx) < 0.4 ? -0.6 : -Math.abs(b.vx);
        } else {
          b.x = rect.right + b.r;
          b.vx = Math.abs(b.vx) < 0.4 ? 0.6 : Math.abs(b.vx);
        }
        b.squash = clamp(b.squash + 0.24, 0, 0.32);
        return;
      }

      var closestX = clamp(b.x, rect.left, rect.right);
      var closestY = clamp(b.y, rect.top, rect.bottom);
      var dx = b.x - closestX, dy = b.y - closestY;
      var dist = Math.hypot(dx, dy);
      if (dist >= b.r) return;

      var nx = dx / dist, ny = dy / dist;
      b.x += nx * (b.r - dist);
      b.y += ny * (b.r - dist);

      var dot = b.vx * nx + b.vy * ny;
      if (dot < 0) {
        b.vx -= 2 * dot * nx * RESTITUTION;
        b.vy -= 2 * dot * ny * RESTITUTION;
      }
      var speed = Math.hypot(b.vx, b.vy);
      b.squash = clamp(b.squash + speed * 0.12, 0, 0.32);
      spawnRipple(closestX, closestY, paramsFor(b.person.id).hue, clamp(speed / MAX_SPEED, 0.2, 1));
    }

    function step(now) {
      var obstacle = heroObstacleRect();
      var footerObstacle = footerObstacleRect();
      var headerObstacle = headerObstacleRect();

      for (var i = 0; i < bodies.length; i++) {
        var b = bodies[i];
        if (!b.dragging) {
          b.x += b.vx;
          b.y += b.vy;
          b.vx += (Math.random() - 0.5) * 0.04;
          b.vy += (Math.random() - 0.5) * 0.04;
          b.vx *= 0.995;
          b.vy *= 0.995;

          var sp = Math.hypot(b.vx, b.vy);
          if (sp > MAX_SPEED) {
            b.vx = (b.vx / sp) * MAX_SPEED;
            b.vy = (b.vy / sp) * MAX_SPEED;
          } else if (sp < 0.3) {
            var a = Math.random() * Math.PI * 2;
            b.vx += Math.cos(a) * 0.2;
            b.vy += Math.sin(a) * 0.2;
          }

          if (b.x - b.r < 0) {
            b.x = b.r; b.vx = Math.abs(b.vx) * RESTITUTION;
            b.squash = clamp(b.squash + Math.abs(b.vx) * 0.12, 0, 0.32);
            spawnRipple(0, b.y, paramsFor(b.person.id).hue, clamp(Math.abs(b.vx) / MAX_SPEED, 0.2, 1));
          } else if (b.x + b.r > W) {
            b.x = W - b.r; b.vx = -Math.abs(b.vx) * RESTITUTION;
            b.squash = clamp(b.squash + Math.abs(b.vx) * 0.12, 0, 0.32);
            spawnRipple(W, b.y, paramsFor(b.person.id).hue, clamp(Math.abs(b.vx) / MAX_SPEED, 0.2, 1));
          }
          if (b.y - b.r < 0) {
            b.y = b.r; b.vy = Math.abs(b.vy) * RESTITUTION;
            b.squash = clamp(b.squash + Math.abs(b.vy) * 0.12, 0, 0.32);
            spawnRipple(b.x, 0, paramsFor(b.person.id).hue, clamp(Math.abs(b.vy) / MAX_SPEED, 0.2, 1));
          } else if (b.y + b.r > H) {
            b.y = H - b.r; b.vy = -Math.abs(b.vy) * RESTITUTION;
            b.squash = clamp(b.squash + Math.abs(b.vy) * 0.12, 0, 0.32);
            spawnRipple(b.x, H, paramsFor(b.person.id).hue, clamp(Math.abs(b.vy) / MAX_SPEED, 0.2, 1));
          }

          resolveRectCollision(b, obstacle);
          resolveRectCollision(b, footerObstacle);
          resolveRectCollision(b, headerObstacle);
        } else {
          b.x = clamp(b.x, b.r, W - b.r);
          b.y = clamp(b.y, b.r, H - b.r);
        }
      }

      for (var i2 = 0; i2 < bodies.length; i2++) {
        for (var j = i2 + 1; j < bodies.length; j++) {
          var a2 = bodies[i2], b2 = bodies[j];
          var dx = b2.x - a2.x, dy = b2.y - a2.y;
          var dist = Math.hypot(dx, dy) || 0.0001;
          var minDist = a2.r + b2.r;
          if (dist < minDist) {
            var nx = dx / dist, ny = dy / dist;
            var overlap = minDist - dist;
            var invA = a2.dragging ? 0 : 1 / a2.mass;
            var invB = b2.dragging ? 0 : 1 / b2.mass;
            var invSum = invA + invB || 1;

            a2.x -= nx * overlap * (invA / invSum);
            a2.y -= ny * overlap * (invA / invSum);
            b2.x += nx * overlap * (invB / invSum);
            b2.y += ny * overlap * (invB / invSum);

            var rvx = b2.vx - a2.vx, rvy = b2.vy - a2.vy;
            var relN = rvx * nx + rvy * ny;
            if (relN < 0) {
              var imp = (-(1 + RESTITUTION) * relN) / invSum;
              var ix = imp * nx, iy = imp * ny;
              a2.vx -= ix * invA; a2.vy -= iy * invA;
              b2.vx += ix * invB; b2.vy += iy * invB;

              var strength = clamp(Math.abs(relN) / MAX_SPEED, 0.25, 1);
              a2.squash = clamp(a2.squash + strength * 0.28, 0, 0.32);
              b2.squash = clamp(b2.squash + strength * 0.28, 0, 0.32);
              spawnRipple(a2.x + nx * a2.r, a2.y + ny * a2.r, paramsFor(a2.person.id).hue, strength);
            }
          }
        }
      }

      var t = now / 1000;
      for (var i3 = 0; i3 < bodies.length; i3++) {
        var bb = bodies[i3];
        var speed2 = Math.hypot(bb.vx, bb.vy);
        var angle2 = Math.atan2(bb.vy, bb.vx);
        var deform = clamp(speed2 * 0.02, 0, 0.07) + bb.squash;
        var breathe = 1 + Math.sin(t * 1.6 + bb.phase) * 0.02;
        var grab = bb.dragging ? 1.06 : 1;
        var sx = (1 + deform) * breathe * grab;
        var sy = (1 - deform) * breathe * grab;
        bb.el.style.transform =
          "translate(" + (bb.x - bb.r) + "px, " + (bb.y - bb.r) + "px) " +
          "rotate(" + angle2 + "rad) scale(" + sx + ", " + sy + ") rotate(" + (-angle2) + "rad)";
        bb.el.style.zIndex = bb.dragging ? "5" : "1";
        bb.squash *= 0.9;
      }

      if (ctx) {
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, W, H);
        for (var k = ripples.length - 1; k >= 0; k--) {
          var rp = ripples[k];
          rp.r += (rp.maxR - rp.r) * 0.14;
          rp.life -= 0.045;
          if (rp.life <= 0) { ripples.splice(k, 1); continue; }
          var al = rp.life * 0.5;
          ctx.beginPath();
          ctx.arc(rp.x, rp.y, rp.r, 0, Math.PI * 2);
          ctx.strokeStyle = "hsla(" + rp.hue + ", 90%, 72%, " + al + ")";
          ctx.lineWidth = 2 * rp.life + 0.5;
          ctx.stroke();
        }
        for (var i4 = 0; i4 < bodies.length; i4++) {
          var gb = bodies[i4];
          if (!gb.dragging) continue;
          var hue2 = paramsFor(gb.person.id).hue;
          var pulse = 1 + Math.sin(t * 6) * 0.04;
          var gr = gb.r * 1.25 * pulse;
          var grad = ctx.createRadialGradient(gb.x, gb.y, gb.r * 0.6, gb.x, gb.y, gr);
          grad.addColorStop(0, "hsla(" + hue2 + ", 95%, 75%, 0)");
          grad.addColorStop(0.7, "hsla(" + hue2 + ", 95%, 72%, 0.22)");
          grad.addColorStop(1, "hsla(" + hue2 + ", 95%, 72%, 0)");
          ctx.beginPath();
          ctx.arc(gb.x, gb.y, gr, 0, Math.PI * 2);
          ctx.fillStyle = grad;
          ctx.fill();
        }
      }

      requestAnimationFrame(step);
    }

    window.addEventListener("pointermove", function (e) {
      if (!dragId) return;
      var body = bodies.filter(function (b) { return b.person.id === dragId; })[0];
      if (!body) return;
      var rect = field.getBoundingClientRect();
      var nx = clamp(e.clientX - rect.left - dragOffX, body.r, W - body.r);
      var ny = clamp(e.clientY - rect.top - dragOffY, body.r, H - body.r);
      body.vx = clamp(nx - body.x, -MAX_SPEED, MAX_SPEED);
      body.vy = clamp(ny - body.y, -MAX_SPEED, MAX_SPEED);
      if (Math.abs(nx - body.x) + Math.abs(ny - body.y) > 2) moved = true;
      body.x = nx;
      body.y = ny;
    });

    function endDrag() {
      if (!dragId) return;
      var body = bodies.filter(function (b) { return b.person.id === dragId; })[0];
      if (body) body.dragging = false;
      dragId = null;
    }
    window.addEventListener("pointerup", endDrag);
    window.addEventListener("pointercancel", endDrag);

    var resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(build, 150);
    });

    build();
    requestAnimationFrame(step);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
