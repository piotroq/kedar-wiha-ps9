/**
 * KEDAR-WIHA.pl — megamenu-init.js
 * Inicjalizacja i rozszerzenie funkcji Megamenu (pos_megamenu)
 * oraz Vertical Megamenu (pos_vertical_megamenu) dla motywu Optima 3.3.0
 *
 * Wersja: 1.0.0
 * Środowisko: PrestaShop 9.0.3 | Bootstrap 4.x | jQuery (dostępne globalnie)
 *
 * WAŻNE: Ten plik musi być dołączony przez moduł lub hook displayBeforeBodyClosingTag
 * Patrz: moduł insertcodeheadfooter, lub wklej w polu "Custom JS" motywu.
 *
 * Zależności: jQuery (ładowane przez Optima), Font Awesome 5 (ładowane przez Optima)
 */

(function ($) {
  'use strict';

  // ============================================================
  // KONFIGURACJA GLOBALNA
  // ============================================================
  var KW_MM_CONFIG = {
    /** Czas opóźnienia przed zamknięciem dropdownu (ms) */
    closeDelay: 220,
    /** Klasy CSS — dostosuj jeśli Optima używa innych selektorów */
    selectors: {
      megamenu:         '#pos_megamenu, .pos-megamenu-wrapper',
      megamenuItem:     '#pos_megamenu ul.megamenu > li',
      megamenuContent:  '.megamenu-content, .sub-menu',
      vmm:              '#pos_vertical_megamenu, .pos-vertical-megamenu',
      vmmItem:          '#pos_vertical_megamenu ul > li, .pos-vertical-megamenu ul > li',
      vmmLink:          '#pos_vertical_megamenu ul > li > a',
      vmmFlyout:        '.sub-menu'
    },
    /** Breakpoint mobilny (px) */
    mobileBreakpoint: 1024,
    /** Dodaj klasę vmm-* do elementów Optimy dla naszych styli CSS */
    addCssClasses: true
  };

  // ============================================================
  // SEKCJA 1: NARZĘDZIA POMOCNICZE
  // ============================================================

  /**
   * Sprawdza czy urządzenie jest mobilne / tablet
   * @returns {boolean}
   */
  function isMobile() {
    return window.innerWidth < KW_MM_CONFIG.mobileBreakpoint;
  }

  /**
   * Debounce — opóźnia wykonanie funkcji
   * @param {Function} fn
   * @param {number} delay
   * @returns {Function}
   */
  function debounce(fn, delay) {
    var timer;
    return function () {
      clearTimeout(timer);
      timer = setTimeout(fn, delay);
    };
  }

  // ============================================================
  // SEKCJA 2: HORIZONTAL MEGAMENU — rozszerzenia
  // ============================================================

  var HorizontalMegamenu = {

    /** Timery opóźnienia zamknięcia (per pozycja menu) */
    closeTimers: {},

    init: function () {
      this.addCssClasses();
      this.bindHoverEvents();
      this.bindKeyboardNav();
      this.addMobileToggle();
      this.markActivePage();
      console.log('[KEDAR-MM] Horizontal megamenu zainicjalizowany.');
    },

    /**
     * Dodaje klasy CSS dla naszych styli do elementów Optima
     */
    addCssClasses: function () {
      if (!KW_MM_CONFIG.addCssClasses) return;

      $(KW_MM_CONFIG.selectors.megamenuItem).each(function () {
        var $item = $(this);
        var $link = $item.children('a, span');
        var $content = $item.children(KW_MM_CONFIG.selectors.megamenuContent);

        // Dodaj klasę do linku top-level jeśli ma children
        if ($content.length) {
          $link.addClass('has-dropdown');

          // Dodaj ikonę chevron jeśli brak
          if (!$link.find('.mm-chevron, .arrow').length) {
            $link.append('<i class="fas fa-chevron-down mm-chevron" aria-hidden="true"></i>');
          }

          // Ustaw atrybuty ARIA
          $link.attr({
            'aria-haspopup': 'true',
            'aria-expanded': 'false'
          });

          $content.attr('aria-hidden', 'true');
        }
      });
    },

    /**
     * Obsługa hover z opóźnieniem zamknięcia (desktop only)
     */
    bindHoverEvents: function () {
      if (isMobile()) return;

      var self = this;

      $(KW_MM_CONFIG.selectors.megamenuItem).each(function () {
        var $item = $(this);
        var itemId = $item.index();

        $item.on('mouseenter', function () {
          // Anuluj timer zamknięcia jeśli istnieje
          clearTimeout(self.closeTimers[itemId]);

          // Zaktualizuj ARIA
          $item.children('a, span').attr('aria-expanded', 'true');
          $item.children(KW_MM_CONFIG.selectors.megamenuContent).attr('aria-hidden', 'false');

        }).on('mouseleave', function () {
          // Opóźnione zamknięcie — pozwala najechać na dropdown
          self.closeTimers[itemId] = setTimeout(function () {
            $item.children('a, span').attr('aria-expanded', 'false');
            $item.children(KW_MM_CONFIG.selectors.megamenuContent).attr('aria-hidden', 'true');
          }, KW_MM_CONFIG.closeDelay);
        });

        // Zapobiega zamknięciu przy hoverze na dropdown
        $item.children(KW_MM_CONFIG.selectors.megamenuContent).on('mouseenter', function () {
          clearTimeout(self.closeTimers[itemId]);
        }).on('mouseleave', function () {
          self.closeTimers[itemId] = setTimeout(function () {
            $item.children('a, span').attr('aria-expanded', 'false');
            $item.children(KW_MM_CONFIG.selectors.megamenuContent).attr('aria-hidden', 'true');
          }, KW_MM_CONFIG.closeDelay);
        });
      });
    },

    /**
     * Nawigacja klawiaturą (WCAG 2.1)
     */
    bindKeyboardNav: function () {
      $(KW_MM_CONFIG.selectors.megamenuItem).on('keydown', '> a, > span', function (e) {
        var $item = $(this).closest('li');
        var $content = $item.children(KW_MM_CONFIG.selectors.megamenuContent);

        switch (e.key) {
          case 'Enter':
          case ' ':
            if ($content.length) {
              e.preventDefault();
              var isExpanded = $(this).attr('aria-expanded') === 'true';
              $(this).attr('aria-expanded', !isExpanded ? 'true' : 'false');
              $content.attr('aria-hidden', !isExpanded ? 'false' : 'true');
              if (!isExpanded) {
                $content.find('a:first').focus();
              }
            }
            break;

          case 'Escape':
            $(this).attr('aria-expanded', 'false');
            $content.attr('aria-hidden', 'true');
            $(this).focus();
            break;
        }
      });

      // Zamknij przy kliknięciu poza menu
      $(document).on('click.kwMegamenu', function (e) {
        if (!$(e.target).closest(KW_MM_CONFIG.selectors.megamenu).length) {
          $(KW_MM_CONFIG.selectors.megamenuItem).children('a, span').attr('aria-expanded', 'false');
        }
      });
    },

    /**
     * Accordion dla urządzeń mobilnych
     */
    addMobileToggle: function () {
      $(KW_MM_CONFIG.selectors.megamenuItem).on('click.kwMobileAccordion', '> a.has-dropdown', function (e) {
        if (!isMobile()) return;

        var $item = $(this).closest('li');
        var $content = $item.children(KW_MM_CONFIG.selectors.megamenuContent);

        if ($content.length) {
          e.preventDefault();
          $item.toggleClass('open');
          $content.stop(true, true).slideToggle(250);
          $(this).attr('aria-expanded', $item.hasClass('open') ? 'true' : 'false');
        }
      });
    },

    /**
     * Oznacza aktywną stronę w menu (podkreślenie)
     */
    markActivePage: function () {
      var currentPath = window.location.pathname;

      $(KW_MM_CONFIG.selectors.megamenuItem).each(function () {
        var $links = $(this).find('a');
        $links.each(function () {
          if ($(this).attr('href') === currentPath) {
            $(this).closest('li').addClass('active');
          }
        });
      });
    }
  };

  // ============================================================
  // SEKCJA 3: VERTICAL MEGAMENU — rozszerzenia
  // ============================================================

  var VerticalMegamenu = {

    /** Timery opóźnienia zamknięcia flyout */
    flyoutTimer: null,

    /** Aktualnie otwarty flyout */
    $activeFlyout: null,

    init: function () {
      this.addCssClasses();
      this.bindFlyoutHover();
      this.positionFlyouts();
      this.bindMobileAccordion();
      this.injectIcons();
      this.addCategoryCounters();

      // Repozycjonuj flyouty po resize
      $(window).on('resize.kwVmm', debounce(function () {
        VerticalMegamenu.positionFlyouts();
      }, 150));

      console.log('[KEDAR-VMM] Vertical megamenu zainicjalizowany.');
    },

    /**
     * Dodaje klasy CSS dla naszych styli
     */
    addCssClasses: function () {
      if (!KW_MM_CONFIG.addCssClasses) return;

      var $vmm = $(KW_MM_CONFIG.selectors.vmm);
      $vmm.addClass('pos-vertical-megamenu');

      $(KW_MM_CONFIG.selectors.vmmItem).each(function () {
        var $item = $(this);
        var $link = $item.children('a');
        var $flyout = $item.children(KW_MM_CONFIG.selectors.vmmFlyout);

        $item.addClass('vmm-item');
        $link.addClass('vmm-link');
        $flyout.addClass('vmm-flyout');

        // Dodaj strzałkę do linków z flyoutem
        if ($flyout.length && !$link.find('.vmm-arrow').length) {
          $link.append('<i class="fas fa-chevron-right vmm-arrow" aria-hidden="true"></i>');
        }
      });
    },

    /**
     * Hover flyout z opóźnieniem (desktop)
     */
    bindFlyoutHover: function () {
      if (isMobile()) return;

      var self = this;

      $(KW_MM_CONFIG.selectors.vmmItem).on('mouseenter.kwVmm', function () {
        clearTimeout(self.flyoutTimer);

        var $item = $(this);
        var $flyout = $item.children('.vmm-flyout, .sub-menu');

        // Tylko na desktop
        if (!isMobile() && $flyout.length) {
          self.$activeFlyout = $flyout;
        }

      }).on('mouseleave.kwVmm', function () {
        self.flyoutTimer = setTimeout(function () {
          self.$activeFlyout = null;
        }, KW_MM_CONFIG.closeDelay);
      });
    },

    /**
     * Dynamiczne pozycjonowanie flyoutów
     * (zapobiega wychodzeniu poza ekran)
     */
    positionFlyouts: function () {
      if (isMobile()) return;

      $(KW_MM_CONFIG.selectors.vmmItem).each(function () {
        var $item = $(this);
        var $flyout = $item.children('.vmm-flyout, .sub-menu');

        if (!$flyout.length) return;

        var itemOffset = $item.offset();
        var flyoutWidth = parseInt(getComputedStyle(document.documentElement)
          .getPropertyValue('--vmm-flyout-width')) || 680;
        var windowWidth = $(window).width();

        // Sprawdź czy flyout wychodzi poza prawą krawędź
        if (itemOffset && (itemOffset.left + flyoutWidth + flyoutWidth) > windowWidth) {
          // Flyout w lewo zamiast w prawo
          $flyout.css({
            'left': 'auto',
            'right': '100%',
            'border-radius': '4px 0 0 4px'
          });
        } else {
          $flyout.css({
            'left': '',
            'right': '',
            'border-radius': '0 4px 4px 0'
          });
        }
      });
    },

    /**
     * Accordion mobile dla vertical menu
     */
    bindMobileAccordion: function () {
      if (!isMobile()) return;

      $(KW_MM_CONFIG.selectors.vmmItem).on('click.kwVmmMobile', '> a', function (e) {
        if (!isMobile()) return;

        var $item = $(this).closest('li');
        var $flyout = $item.children('.sub-menu, .vmm-flyout');

        if ($flyout.length) {
          e.preventDefault();
          $item.toggleClass('open');
          $flyout.stop(true, true).slideToggle(250);
        }
      });
    },

    /**
     * Wstrzykuje ikony Font Awesome do linków kategorii
     * na podstawie data-icon na elemencie lub mapowania nazwy kategorii
     */
    injectIcons: function () {
      var iconMap = {
        'wkrętaki':       'fas fa-bolt',
        'vde':            'fas fa-bolt',
        'izolowane':      'fas fa-shield-alt',
        'klucze':         'fas fa-wrench',
        'dynamometryczne':'fas fa-cog',
        'kombinerki':     'fas fa-grip-lines-vertical',
        'szczypce':       'fas fa-cut',
        'zestawy':        'fas fa-toolbox',
        'akcesoria':      'fas fa-puzzle-piece',
        'końcówki':       'fas fa-pencil-ruler',
        'etui':           'fas fa-briefcase',
        'torby':          'fas fa-shopping-bag',
        'stripery':       'fas fa-scissors',
        'nożyki':         'fas fa-cut',
        'fotowoltaika':   'fas fa-solar-panel',
        'ev':             'fas fa-charging-station',
        'elektryczne':    'fas fa-plug',
        'imbusowe':       'fas fa-key',
        'nasadowe':       'fas fa-dot-circle'
      };

      $(KW_MM_CONFIG.selectors.vmmLink).each(function () {
        var $link = $(this);

        // Nie dodawaj jeśli już jest ikona
        if ($link.find('.fas, .far, .fab, .vmm-icon').length) return;

        var linkText = $link.text().toLowerCase().trim();
        var iconClass = null;

        // Znajdź pasującą ikonę
        for (var keyword in iconMap) {
          if (iconMap.hasOwnProperty(keyword) && linkText.indexOf(keyword) !== -1) {
            iconClass = iconMap[keyword];
            break;
          }
        }

        // Domyślna ikona
        if (!iconClass) {
          iconClass = 'fas fa-tag';
        }

        // Wstaw ikonę przed tekstem
        $link.prepend('<i class="' + iconClass + ' vmm-icon" aria-hidden="true"></i>');
      });
    },

    /**
     * Dodaje liczniki produktów do kategorii (jeśli dane są dostępne)
     * Wymaga PrestaShop category_count data
     */
    addCategoryCounters: function () {
      $(KW_MM_CONFIG.selectors.vmmItem).each(function () {
        var $item = $(this);
        var count = $item.data('product-count');

        if (count && count > 0) {
          $item.children('.vmm-link').append(
            '<span class="vmm-count" style="margin-left:auto;font-size:0.65rem;color:#888;font-family:\'Inter\',sans-serif;">' +
            count +
            '</span>'
          );
        }
      });
    }
  };

  // ============================================================
  // SEKCJA 4: EFEKTY PREMIUM — Smooth reveal, animacje
  // ============================================================

  var MegamenuEffects = {

    init: function () {
      this.initPromoHover();
      this.initDropdownEntrance();
    },

    /**
     * Efekt parallax na panelach promo przy hoverze (subtelny)
     */
    initPromoHover: function () {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

      $('.mm-promo-panel, .vmm-flyout-promo').on('mousemove', function (e) {
        var $panel = $(this);
        var $img = $panel.find('img');

        if (!$img.length) return;

        var rect = this.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width - 0.5) * 6; // max 3deg
        var y = ((e.clientY - rect.top) / rect.height - 0.5) * 6;

        $img.css('transform', 'scale(1.04) translate(' + x + 'px, ' + y + 'px)');

      }).on('mouseleave', function () {
        $(this).find('img').css('transform', '');
      });
    },

    /**
     * Entrance animation dla linków po otwarciu dropdownu
     */
    initDropdownEntrance: function () {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

      $(KW_MM_CONFIG.selectors.megamenuItem).on('mouseenter', function () {
        var $links = $(this).find('.mm-link, .vmm-subcat-list a');
        $links.each(function (i) {
          var $link = $(this);
          $link.css({ opacity: 0, transform: 'translateY(6px)' });
          setTimeout(function () {
            $link.css({
              transition: 'opacity 0.2s ease, transform 0.2s ease',
              opacity: 1,
              transform: 'translateY(0)'
            });
          }, i * 30);
        });
      });
    }
  };

  // ============================================================
  // SEKCJA 5: ACCESSIBILITY — Focus trap w megamenu
  // ============================================================

  var MegamenuA11y = {

    init: function () {
      this.addSkipLink();
      this.bindEscapeKey();
      this.addARIALandmarks();
    },

    addSkipLink: function () {
      if ($('.mm-skip-nav').length) return;

      $('body').prepend(
        '<a href="#main-content" class="mm-skip-nav">Przejdź do treści</a>'
      );
    },

    bindEscapeKey: function () {
      $(document).on('keydown.kwMegamenuEsc', function (e) {
        if (e.key === 'Escape') {
          // Zamknij wszystkie otwarte dropdowny
          $(KW_MM_CONFIG.selectors.megamenuItem).removeClass('open');
          $(KW_MM_CONFIG.selectors.vmmItem).removeClass('open');
        }
      });
    },

    addARIALandmarks: function () {
      $(KW_MM_CONFIG.selectors.megamenu)
        .attr('role', 'navigation')
        .attr('aria-label', 'Nawigacja główna');

      $(KW_MM_CONFIG.selectors.vmm)
        .attr('role', 'navigation')
        .attr('aria-label', 'Kategorie sklepu');
    }
  };

  // ============================================================
  // SEKCJA 6: INICJALIZACJA GŁÓWNA
  // ============================================================

  $(document).ready(function () {

    // Sprawdź czy elementy megamenu istnieją
    if ($(KW_MM_CONFIG.selectors.megamenu).length) {
      HorizontalMegamenu.init();
      MegamenuEffects.init();
      MegamenuA11y.init();
    }

    if ($(KW_MM_CONFIG.selectors.vmm).length) {
      VerticalMegamenu.init();
    }

    // Re-init po zmianie rozmiar okna
    $(window).on('resize.kwMegamenuResize', debounce(function () {
      // Reset mobile accordion przy powrocie do desktop
      if (!isMobile()) {
        $(KW_MM_CONFIG.selectors.megamenuItem).removeClass('open');
        $(KW_MM_CONFIG.selectors.megamenuContent).removeAttr('style');
        $(KW_MM_CONFIG.selectors.vmmItem).removeClass('open');
        $('.vmm-flyout, ' + KW_MM_CONFIG.selectors.vmmFlyout).removeAttr('style');
      }
    }, 200));

  });

  // Eksport globalny (dla debugowania i rozszerzeń)
  window.KW_Megamenu = {
    config: KW_MM_CONFIG,
    horizontal: HorizontalMegamenu,
    vertical: VerticalMegamenu,
    effects: MegamenuEffects,
    a11y: MegamenuA11y
  };

}(jQuery));
