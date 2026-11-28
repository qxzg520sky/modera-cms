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
    var body = document.body;

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'sz-top-btn';
    btn.setAttribute('aria-label', '回到顶部');
    btn.innerHTML = '↑';
    body.appendChild(btn);

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function onScroll() {
      var y = window.pageYOffset || document.documentElement.scrollTop;
      if (y > 520) {
        btn.classList.add('on');
      } else {
        btn.classList.remove('on');
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // 正文首图加载失败时，回退到主题自动封面
    var pool = 6;
    var imgs = document.querySelectorAll('.sz-lead-img, .sz-post-img, .sz-arc-img, .sz-related-img, .sz-article-cover-img');
    Array.prototype.forEach.call(imgs, function (img, i) {
      img.addEventListener('error', function () {
        if (img.dataset.fallbackDone) { return; }
        img.dataset.fallbackDone = '1';
        img.src = '/theme/cover' + ((i % pool) + 1) + '.jpg';
      });
    });
  });
})();
