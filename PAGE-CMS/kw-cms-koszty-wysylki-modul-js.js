// ==========================================================================
// KEDAR-WIHA.pl — Koszty wysyłki — KW CMS Custom JS
// UWAGA: Moduł owija automatycznie w IIFE+try/catch — NIE dodawaj (function(){})()
// jQuery dostępny jako $ | Uruchamia się po jQuery, Bootstrap, Optima.js
// ==========================================================================

'use strict';

var revealSelectors = '.kw-shipping-reveal, .kw-shipping-slide-left, .kw-shipping-slide-right';

/* ── KROK 1: Progressive Enhancement opt-in ────────────────────────────────
   Dodanie klasy .kw-scroll-ready na <body> "odblokowuje" CSS opacity:0.
   Bez tej klasy wszystkie elementy są domyślnie widoczne (fallback).
   ───────────────────────────────────────────────────────────────────────── */
document.body.classList.add('kw-scroll-ready');

/* ── Helper ─────────────────────────────────────────────────────────────── */
function kwShMarkVisible(el) {
  el.classList.add('kw-shipping-visible');
}

/* ── KROK 2: IntersectionObserver — Scroll Reveal ──────────────────────── */
if ('IntersectionObserver' in window) {
  var kwShRevealObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        kwShMarkVisible(entry.target);
        kwShRevealObserver.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.08,
    rootMargin: '0px 0px -20px 0px'
  });

  document.querySelectorAll(revealSelectors).forEach(function(el) {
    kwShRevealObserver.observe(el);
  });

} else {
  /* Fallback — stare przeglądarki bez IntersectionObserver */
  document.querySelectorAll(revealSelectors).forEach(kwShMarkVisible);
}

/* ── KROK 3: Failsafe — po 800ms pokaż wszystko co nadal niewidoczne ────── */
setTimeout(function() {
  document.querySelectorAll(revealSelectors).forEach(function(el) {
    if (!el.classList.contains('kw-shipping-visible')) {
      kwShMarkVisible(el);
    }
  });
}, 800);

/* ── KROK 4: Animated Counter ────────────────────────────────────────────── */
function kwShAnimateCounter(element) {
  var target = parseInt(element.getAttribute('data-target'), 10) || 0;
  var startValue = 0;
  var duration = 2000;
  var startTime = null;

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function step(timestamp) {
    if (!startTime) { startTime = timestamp; }
    var progress = Math.min((timestamp - startTime) / duration, 1);
    var current = Math.floor(startValue + (target - startValue) * easeOutCubic(progress));
    element.textContent = current.toLocaleString('pl-PL');
    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      element.textContent = target.toLocaleString('pl-PL');
    }
  }

  requestAnimationFrame(step);
}

if ('IntersectionObserver' in window) {
  var kwShCounterObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        kwShAnimateCounter(entry.target);
        kwShCounterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  document.querySelectorAll('.kw-shipping-counter').forEach(function(el) {
    kwShCounterObserver.observe(el);
  });
}

/* ── KROK 5: Smooth Scroll dla anchor linków ──────────────────────────────── */
document.querySelectorAll('.kw-shipping-page a[href^="#"]').forEach(function(link) {
  link.addEventListener('click', function(e) {
    var href = this.getAttribute('href');
    if (href && href.length > 1) {
      var targetEl = document.querySelector(href);
      if (targetEl) {
        e.preventDefault();
        var headerOffset = 80;
        var possibleHeaders = [
          '.header-main', '#header', '.l-header',
          '.header-sticky', '.navbar', 'header'
        ];
        for (var i = 0; i < possibleHeaders.length; i++) {
          var hEl = document.querySelector(possibleHeaders[i]);
          if (hEl) {
            headerOffset = hEl.offsetHeight + 12;
            break;
          }
        }
        var elementPosition = targetEl.getBoundingClientRect().top;
        var offsetPosition = elementPosition + window.pageYOffset - headerOffset;
        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
      }
    }
  });
});
