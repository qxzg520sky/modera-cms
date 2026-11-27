document.addEventListener('DOMContentLoaded', function () {
  var body = document.body;
  var btn = document.querySelector('.menu-btn');
  var drawer = document.querySelector('.mobile-nav');

  if (btn) {
    btn.addEventListener('click', function () {
      body.classList.toggle('nav-open');
      var open = body.classList.contains('nav-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  if (drawer) {
    drawer.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        body.classList.remove('nav-open');
      });
    });
  }

  var header = document.querySelector('.site-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 20);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
});
