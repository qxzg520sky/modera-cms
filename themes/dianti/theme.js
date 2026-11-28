document.addEventListener('DOMContentLoaded', function () {
  var body = document.body;
  var btn = document.querySelector('.menu-btn');
  var drawer = document.querySelector('.mobile-nav');
  var close = document.querySelector('.mobile-close');

  function closeMenu() {
    body.classList.remove('nav-open');
    if (btn) { btn.setAttribute('aria-expanded', 'false'); }
  }
  if (btn) {
    btn.addEventListener('click', function () {
      body.classList.toggle('nav-open');
      btn.setAttribute('aria-expanded', body.classList.contains('nav-open') ? 'true' : 'false');
    });
  }
  if (close) { close.addEventListener('click', closeMenu); }
  if (drawer) {
    drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeMenu); });
  }

  var header = document.querySelector('.site-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 20); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
});
