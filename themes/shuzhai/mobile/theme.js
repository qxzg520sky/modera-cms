(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  ready(function () {
    var drawer = document.getElementById('mzDrawer');
    var mask = document.querySelector('[data-mz-mask]');
    var btn = document.querySelector('[data-mz-menu]');
    var closeBtn = document.querySelector('[data-mz-close]');

    function open() {
      if (!drawer) { return; }
      drawer.classList.add('open');
      if (mask) { mask.classList.add('on'); }
      if (btn) { btn.classList.add('on'); btn.setAttribute('aria-expanded', 'true'); }
      document.body.classList.add('mz-lock');
    }

    function close() {
      if (!drawer) { return; }
      drawer.classList.remove('open');
      if (mask) { mask.classList.remove('on'); }
      if (btn) { btn.classList.remove('on'); btn.setAttribute('aria-expanded', 'false'); }
      document.body.classList.remove('mz-lock');
    }

    if (btn) {
      btn.addEventListener('click', function () {
        if (drawer && drawer.classList.contains('open')) { close(); } else { open(); }
      });
    }
    if (closeBtn) { closeBtn.addEventListener('click', close); }
    if (mask) { mask.addEventListener('click', close); }
    Array.prototype.forEach.call(document.querySelectorAll('.mz-drawer a'), function (a) {
      a.addEventListener('click', close);
    });

    var top = document.querySelector('[data-mz-top]');
    function onScroll() {
      if (!top) { return; }
      var y = window.pageYOffset || document.documentElement.scrollTop;
      if (y > 420) { top.classList.add('on'); } else { top.classList.remove('on'); }
    }
    if (top) {
      top.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var pool = 6;
    var imgs = document.querySelectorAll('.mz-hero-img, .mz-thumb-img, .mz-arc-img, .mz-article-img, .mz-related-img');
    Array.prototype.forEach.call(imgs, function (img, i) {
      img.addEventListener('error', function () {
        if (img.dataset.fallbackDone) { return; }
        img.dataset.fallbackDone = '1';
        img.src = '/theme/cover' + ((i % pool) + 1) + '.jpg';
      });
    });
  });
})();
