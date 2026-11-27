(function () {
  'use strict';

  var root = document.documentElement;
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ----------------------------------------------------------------------
     Theme switch
     ---------------------------------------------------------------------- */
  function currentTheme() {
    return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }

  function syncThemeButton() {
    var btn = document.querySelector('[data-theme-toggle]');
    if (!btn) return;
    var dark = currentTheme() === 'dark';
    btn.setAttribute('aria-label', dark ? '切换到浅色' : '切换到深色');
    btn.setAttribute('title', dark ? '切换到浅色' : '切换到深色');
  }

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    try { localStorage.setItem('ink-theme', theme); } catch (e) {}
    syncThemeButton();
    if (window.InkCover) window.InkCover.renderAll(true);
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-theme-toggle]');
    if (btn) {
      applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
    }
  });
  syncThemeButton();

  /* ----------------------------------------------------------------------
     Header / menu / scroll
     ---------------------------------------------------------------------- */
  var head = document.querySelector('[data-head]');
  var menuBtn = document.querySelector('[data-menu-toggle]');
  var nav = document.getElementById('siteNav');
  var toTop = document.querySelector('[data-to-top]');

  function onScroll() {
    var y = window.pageYOffset || document.documentElement.scrollTop;
    if (head) head.classList.toggle('scrolled', y > 6);
    if (toTop) toTop.classList.toggle('show', y > 620);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuBtn.setAttribute('aria-label', open ? '关闭菜单' : '打开菜单');
    });
    nav.addEventListener('click', function (e) {
      if (e.target.closest && e.target.closest('a')) {
        nav.classList.remove('open');
        menuBtn.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('click', function (e) {
      if (!nav.classList.contains('open')) return;
      if (nav.contains(e.target) || menuBtn.contains(e.target)) return;
      nav.classList.remove('open');
      menuBtn.setAttribute('aria-expanded', 'false');
    });
  }

  if (toTop) {
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }

  /* ----------------------------------------------------------------------
     Reveal
     ---------------------------------------------------------------------- */
  var reveals = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
  if (reveals.length) {
    if (!('IntersectionObserver' in window) || reduceMotion) {
      reveals.forEach(function (el) { el.classList.add('is-in'); });
    } else {
      var revealIO = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-in');
            revealIO.unobserve(entry.target);
          }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
      reveals.forEach(function (el) { revealIO.observe(el); });
    }
  }

  /* ----------------------------------------------------------------------
     Canvas cover engine
     ---------------------------------------------------------------------- */
  var W = 1600, H = 1000;
  var SERIF = '"Iowan Old Style","Songti SC","Noto Serif SC","Source Han Serif SC",Georgia,serif';
  var SANS = '-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Hiragino Sans GB","Microsoft YaHei",sans-serif';
  var MONO = '"SFMono-Regular",Consolas,"Liberation Mono",monospace';

  var PALETTES = {
    light: [
      { paper: '#f1ede4', ink: '#22251f', muted: '#63685f', line: 'rgba(34,37,31,.14)', accent: '#7d9079', clay: '#b2917d', wash: 'rgba(125,144,121,.16)' },
      { paper: '#f0ece5', ink: '#242520', muted: '#66675f', line: 'rgba(36,37,32,.14)', accent: '#8b8878', clay: '#b49a86', wash: 'rgba(139,136,120,.15)' },
      { paper: '#eeece7', ink: '#232522', muted: '#63675f', line: 'rgba(35,37,34,.14)', accent: '#7c8a89', clay: '#ae9386', wash: 'rgba(124,138,137,.16)' }
    ],
    dark: [
      { paper: '#171a17', ink: '#e9e7df', muted: '#b2b9af', line: 'rgba(233,231,223,.15)', accent: '#9cb399', clay: '#c4a48e', wash: 'rgba(156,179,153,.16)' },
      { paper: '#191a17', ink: '#e8e6de', muted: '#b4b5ac', line: 'rgba(232,230,222,.15)', accent: '#b0ac98', clay: '#c3aa96', wash: 'rgba(176,172,152,.15)' },
      { paper: '#161a1a', ink: '#e7e7e1', muted: '#b0b8b7', line: 'rgba(231,231,225,.15)', accent: '#9aadae', clay: '#bda398', wash: 'rgba(154,173,174,.16)' }
    ]
  };

  function hash(str) {
    var h = 2166136261 >>> 0;
    for (var i = 0; i < str.length; i++) {
      h ^= str.charCodeAt(i);
      h = Math.imul(h, 16777619);
    }
    return h >>> 0;
  }

  function mulberry32(a) {
    return function () {
      a |= 0;
      a = a + 0x6D2B79F5 | 0;
      var t = Math.imul(a ^ a >>> 15, 1 | a);
      t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
      return ((t ^ t >>> 14) >>> 0) / 4294967296;
    };
  }

  function spacedText(ctx, text, x, y, spacing, align) {
    var chars = Array.from(text);
    var widths = chars.map(function (ch) { return ctx.measureText(ch).width; });
    var total = widths.reduce(function (a, b) { return a + b; }, 0) + spacing * Math.max(0, chars.length - 1);
    var start = x;
    if (align === 'center') start = x - total / 2;
    if (align === 'right') start = x - total;
    ctx.textAlign = 'left';
    for (var i = 0; i < chars.length; i++) {
      ctx.fillText(chars[i], start, y);
      start += widths[i] + spacing;
    }
    return total;
  }

  function wrapLines(ctx, text, maxWidth, maxLines) {
    var chars = Array.from(text || '');
    var lines = [];
    var line = '';
    var noStart = /^[，。、；：！？）》」』”’…,.;:!?)]/;
    for (var i = 0; i < chars.length; i++) {
      var ch = chars[i];
      if (ch === '\n') {
        lines.push(line);
        line = '';
        if (lines.length >= maxLines) break;
        continue;
      }
      var test = line + ch;
      if (ctx.measureText(test).width > maxWidth && line !== '') {
        lines.push(line);
        if (noStart.test(ch)) {
          lines[lines.length - 1] += ch;
          line = '';
        } else {
          line = ch;
        }
        if (lines.length >= maxLines) break;
      } else {
        line = test;
      }
    }
    if (line && lines.length < maxLines) lines.push(line);
    if (lines.length === maxLines && chars.length > 0) {
      var consumed = lines.join('').length;
      if (consumed < chars.length) {
        var last = lines[maxLines - 1];
        while (last.length > 1 && ctx.measureText(last + '…').width > maxWidth) {
          last = last.slice(0, -1);
        }
        lines[maxLines - 1] = last.replace(/[，。；、,.;:\s]+$/, '') + '…';
      }
    }
    return lines;
  }

  function drawGeometric(ctx, variant, p, rnd) {
    var accent = p.accent;
    ctx.save();
    if (variant === 0) {
      ctx.fillStyle = p.wash;
      ctx.beginPath();
      ctx.arc(1205, 288, 226, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = accent;
      ctx.globalAlpha = 0.56;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(1205, 288, 300, 0, Math.PI * 2);
      ctx.stroke();
      ctx.globalAlpha = 0.35;
      ctx.beginPath();
      ctx.moveTo(880, 1000);
      ctx.lineTo(1600, 410);
      ctx.stroke();
      ctx.globalAlpha = 0.22;
      ctx.fillStyle = accent;
      ctx.beginPath();
      ctx.arc(318, 855, 7, 0, Math.PI * 2);
      ctx.fill();
    } else if (variant === 1) {
      ctx.fillStyle = p.wash;
      ctx.fillRect(1080, 0, 520, H);
      ctx.strokeStyle = p.line;
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(120, 162); ctx.lineTo(1480, 162);
      ctx.moveTo(120, 878); ctx.lineTo(1480, 878);
      ctx.stroke();
      ctx.fillStyle = accent;
      ctx.globalAlpha = 0.5;
      ctx.fillRect(120, 162, 54, 2);
      ctx.globalAlpha = 0.22;
      for (var i = 0; i < 5; i++) {
        ctx.fillRect(1250 + i * 28, 700, 8, 8);
      }
    } else if (variant === 2) {
      ctx.fillStyle = p.wash;
      ctx.beginPath();
      ctx.arc(800, 610, 430, Math.PI, 0);
      ctx.lineTo(1230, 1000);
      ctx.lineTo(370, 1000);
      ctx.closePath();
      ctx.fill();
      ctx.strokeStyle = accent;
      ctx.globalAlpha = 0.38;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(800, 610, 430, Math.PI, 0);
      ctx.stroke();
      ctx.globalAlpha = 0.25;
      ctx.beginPath();
      ctx.moveTo(120, 610); ctx.lineTo(370, 610);
      ctx.moveTo(1230, 610); ctx.lineTo(1480, 610);
      ctx.stroke();
    } else if (variant === 3) {
      ctx.fillStyle = p.wash;
      ctx.beginPath();
      ctx.moveTo(0, 720);
      ctx.lineTo(1600, 430);
      ctx.lineTo(1600, 1000);
      ctx.lineTo(0, 1000);
      ctx.closePath();
      ctx.fill();
      ctx.strokeStyle = accent;
      ctx.globalAlpha = 0.5;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.moveTo(0, 720);
      ctx.lineTo(1600, 430);
      ctx.stroke();
      ctx.globalAlpha = 0.2;
      ctx.fillStyle = accent;
      for (var y = 170; y <= 330; y += 40) {
        for (var x = 1180; x <= 1460; x += 40) {
          ctx.beginPath();
          ctx.arc(x, y, 3.2, 0, Math.PI * 2);
          ctx.fill();
        }
      }
    } else if (variant === 4) {
      ctx.fillStyle = p.wash;
      ctx.fillRect(0, 220, 680, 16);
      ctx.fillRect(0, 500, 1120, 16);
      ctx.fillRect(0, 780, 460, 16);
      ctx.strokeStyle = accent;
      ctx.globalAlpha = 0.34;
      ctx.lineWidth = 2;
      ctx.strokeRect(1138, 216, 342, 342);
      ctx.globalAlpha = 0.5;
      ctx.fillRect(120, 960, 120, 2);
    } else {
      ctx.fillStyle = p.wash;
      ctx.beginPath();
      ctx.arc(-30, 90, 395, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = accent;
      ctx.globalAlpha = 0.5;
      ctx.lineWidth = 2;
      ctx.beginPath();
      ctx.arc(-30, 90, 470, 0, Math.PI * 2);
      ctx.stroke();
      ctx.strokeStyle = p.line;
      ctx.globalAlpha = 1;
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(1210, 90); ctx.lineTo(1210, 860);
      ctx.moveTo(1330, 90); ctx.lineTo(1330, 860);
      ctx.stroke();
      ctx.fillStyle = accent;
      ctx.globalAlpha = 0.35;
      ctx.fillRect(120, 935, 180, 2);
    }
    ctx.restore();
  }

  function drawNoise(ctx, p, rnd, dark) {
    ctx.save();
    ctx.fillStyle = dark ? '#e9e7df' : '#1c1f1b';
    ctx.globalAlpha = dark ? 0.055 : 0.035;
    for (var i = 0; i < 2600; i++) {
      var x = Math.floor(rnd() * W);
      var y = Math.floor(rnd() * H);
      var s = rnd() > 0.72 ? 1.5 : 1;
      ctx.fillRect(x, y, s, s);
    }
    ctx.restore();
  }

  function drawCover(canvas) {
    var data = canvas.dataset;
    var title = (data.coverTitle || '未命名笔记').trim();
    var summary = (data.coverSummary || '').trim();
    var cat = (data.coverCat || '笔记').trim();
    var date = (data.coverDate || '').trim();
    var seedText = data.coverSeed || (title + summary);
    var dark = currentTheme() === 'dark';
    var seed = hash(seedText);
    var rnd = mulberry32(seed);
    var variant = seed % 6;
    var palette = PALETTES[dark ? 'dark' : 'light'][seed % 3];
    var p = palette;

    var ctx = canvas.getContext('2d');
    if (!ctx) return;
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, W, H);

    var grad = ctx.createLinearGradient(0, 0, W, H);
    grad.addColorStop(0, p.paper);
    grad.addColorStop(0.58, p.paper);
    grad.addColorStop(1, dark ? '#1d211d' : '#ece7dc');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, W, H);

    drawGeometric(ctx, variant, p, rnd);
    drawNoise(ctx, p, rnd, dark);

    var align = variant === 2 ? 'center' : (variant === 4 ? 'right' : 'left');
    var x = align === 'center' ? W / 2 : (align === 'right' ? W - 120 : 120);
    var maxW = align === 'center' ? 1160 : 1080;
    var titleY = [380, 238, 300, 168, 250, 380][variant];
    var titleSize = title.length > 34 ? 80 : (title.length > 22 ? 92 : 104);
    var lh = titleSize * 1.17;

    ctx.fillStyle = p.ink;
    ctx.textBaseline = 'alphabetic';
    ctx.font = '600 ' + titleSize + 'px ' + SERIF;
    ctx.textAlign = align;
    var titleLines = wrapLines(ctx, title, maxW, 3);
    for (var i = 0; i < titleLines.length; i++) {
      ctx.fillText(titleLines[i], x, titleY + i * lh);
    }

    var afterTitle = titleY + Math.max(1, titleLines.length) * lh + 5;
    if (summary && afterTitle < 830) {
      ctx.fillStyle = p.muted;
      ctx.font = '400 48px ' + SANS;
      ctx.textAlign = align;
      var summaryLines = wrapLines(ctx, summary, align === 'center' ? 1100 : 1040, 2);
      for (var j = 0; j < summaryLines.length; j++) {
        ctx.fillText(summaryLines[j], x, afterTitle + 38 + j * 64);
      }
    }

    ctx.font = '500 30px ' + SANS;
    ctx.fillStyle = p.muted;
    var catY = [126, 112, 104, 106, 112, 112][variant];
    if (align === 'center') {
      spacedText(ctx, cat.toUpperCase(), x, catY, 5, 'center');
    } else if (align === 'right') {
      spacedText(ctx, cat.toUpperCase(), x, catY, 5, 'right');
    } else {
      spacedText(ctx, cat.toUpperCase(), x, catY, 5, 'left');
    }

    ctx.strokeStyle = p.line;
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(120, 910);
    ctx.lineTo(W - 120, 910);
    ctx.stroke();

    ctx.font = '400 26px ' + MONO;
    ctx.fillStyle = p.muted;
    ctx.textAlign = 'left';
    ctx.fillText(date || 'INK / NOTES', 120, 956);

    ctx.textAlign = 'right';
    ctx.fillStyle = p.ink;
    ctx.font = '500 26px ' + MONO;
    var no = data.coverNo || ('NO.' + (seed % 97 + 1));
    ctx.fillText(no, W - 120, 956);

    ctx.textAlign = 'left';
    ctx.fillStyle = p.accent;
    ctx.fillRect(120, 956 - 2, 52, 2);

    canvas.dataset.rendered = '1';
    canvas.parentNode.classList.add('is-ready');
  }

  function renderCanvas(canvas) {
    try {
      drawCover(canvas);
    } catch (e) {
      canvas.parentNode.classList.add('is-ready');
    }
  }

  function initCover() {
    var wrappers = Array.prototype.slice.call(document.querySelectorAll('[data-cover]'));
    if (!wrappers.length) return;

    if (!('IntersectionObserver' in window) || reduceMotion) {
      wrappers.forEach(function (wrap) { renderCanvas(wrap.querySelector('canvas')); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var wrap = entry.target;
        var canvas = wrap.querySelector('canvas');
        if (canvas && wrap.classList.contains('is-auto')) renderCanvas(canvas);
        io.unobserve(wrap);
      });
    }, { rootMargin: '240px 0px', threshold: 0.01 });

    wrappers.forEach(function (wrap) {
      var canvas = wrap.querySelector('canvas');
      var img = wrap.querySelector('img');
      if (img) {
        var failed = function () {
          wrap.classList.add('is-fallback');
          if (canvas) renderCanvas(canvas);
        };
        if (img.complete && img.naturalWidth === 0) failed();
        else img.addEventListener('error', failed, { once: true });
      } else {
        io.observe(wrap);
      }
    });
  }

  window.InkCover = {
    renderAll: function (force) {
      document.querySelectorAll('[data-cover]').forEach(function (wrap) {
        var canvas = wrap.querySelector('canvas');
        if (!canvas) return;
        if (wrap.classList.contains('is-auto') || wrap.classList.contains('is-fallback')) {
          renderCanvas(canvas);
        }
      });
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCover);
  } else {
    initCover();
  }
})();

