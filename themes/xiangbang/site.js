/* 象邦自然系模板 · 交互 */
(function () {
  'use strict';
  var body = document.body;
  var header = document.getElementById('xbHeader');
  var menuBtn = document.querySelector('.xb-menu-btn');
  var mobile = document.getElementById('xbMobileNav');

  function syncHeader() {
    if (header) header.classList.toggle('scrolled', window.scrollY > 20);
  }
  syncHeader();
  window.addEventListener('scroll', syncHeader, { passive: true });

  function setMenu(open) {
    if (!mobile || !menuBtn) return;
    body.classList.toggle('xb-menu-open', open);
    mobile.classList.toggle('on', open);
    mobile.setAttribute('aria-hidden', String(!open));
    menuBtn.setAttribute('aria-expanded', String(open));
  }
  if (menuBtn) menuBtn.addEventListener('click', function () {
    setMenu(!body.classList.contains('xb-menu-open'));
  });
  if (mobile) {
    mobile.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { setMenu(false); });
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setMenu(false);
  });

  var items = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && items.length) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -24px' });
    items.forEach(function (item) { observer.observe(item); });
  } else {
    items.forEach(function (item) { item.classList.add('revealed'); });
  }
})();
