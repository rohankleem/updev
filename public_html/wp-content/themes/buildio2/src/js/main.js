console.log("Main js code");

import HSMegaMenu from '../vendor/hs-mega-menu/dist/hs-mega-menu';
import Swiper from 'swiper/bundle';
//import { Navigation, Pagination } from 'swiper/modules';


// import 'swiper/css';
// import 'swiper/css/navigation';
// import 'swiper/css/pagination';

(function () {
  // INITIALIZATION OF SWIPER
  // =======================================================
  var swiper = new Swiper('.js-swiper-course-hero', {
    //modules: [Navigation, Pagination],

    preloaderClass: 'custom-swiper-lazy-preloader',
    // navigation: {
    //   nextEl: '.js-swiper-course-hero-button-next',
    //   prevEl: '.js-swiper-course-hero-button-prev',

    // },
    slidesPerView: 1,
    loop: 1,
    freeMode: {
      enabled: true,
      sticky: false,
    },
    speed: 3500,
    autoplay: {
      delay: 0,
      disableOnInteraction: false,
    },
    breakpoints: {
      380: {
        slidesPerView: 2,
        spaceBetween: 15,
      },
      580: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      768: {
        slidesPerView: 4,
        spaceBetween: 15,
      },
      1024: {
        slidesPerView: 5,
        spaceBetween: 15,
      },
    },
    on: {
      'imagesReady': function (swiper) {
        const preloader = swiper.el.querySelector('.js-swiper-course-hero-preloader')
        preloader.parentNode.removeChild(preloader)
      }
    }
  });




  var swiper = new Swiper(".swiperBlogSnippets", {
    slidesPerView: 3,
    spaceBetween: 30,
    freeMode: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      380: {
        slidesPerView: 1.3,
        spaceBetween: 15,
      },
      580: {
        slidesPerView: 1.5,
        spaceBetween: 15,
      },
      768: {
        slidesPerView: 2.4,
        spaceBetween: 15,
      },
      1024: {
        slidesPerView: 3.4,
        spaceBetween: 15,
      },
    }
  });
})();




// INITIALIZATION OF MEGA MENU
// =======================================================
document.addEventListener('DOMContentLoaded', function () {
  var megaMenu = document.querySelector('.js-mega-menu');
  if (megaMenu) {
    new HSMegaMenu(megaMenu, {
      eventType: 'hover',
      direction: 'horizontal',
      hideTimeOut: 600
    });
  }
});


document.addEventListener('DOMContentLoaded', function () {

  const marquee = document.querySelector('.stats-marquee');
  if (!marquee) return;

  const scroller = marquee.querySelector('.stats-marquee__scroller');
  const track = marquee.querySelector('.stats-marquee__track');
  if (!scroller || !track) return;

  const speed = 50;

  let dragging = false;
  let startX = 0;
  let startScroll = 0;

  function wrapWidth() {
    return track.scrollWidth;
  }

  function wrap(val, max) {
    val = val % max;
    if (val < 0) val += max;
    return val;
  }

  // --- Drag ---
  scroller.addEventListener('pointerdown', e => {
    dragging = true;
    startX = e.clientX;
    startScroll = scroller.scrollLeft;
    scroller.setPointerCapture(e.pointerId);
  });

  scroller.addEventListener('pointermove', e => {
    if (!dragging) return;
    const w = wrapWidth();
    if (!w) return;

    scroller.scrollLeft = wrap(startScroll - (e.clientX - startX), w);

    // keep the internal float position aligned to manual drag
    scrollFloat = scroller.scrollLeft;
  });

  window.addEventListener('pointerup', () => {
    dragging = false;
  });

  // --- Auto move (SMOOTHER) ---
  let last = null;

  // keep our own float position
  let scrollFloat = scroller.scrollLeft;

  // keep last written integer to reduce redundant writes
  let lastWritten = Math.round(scroller.scrollLeft);

  function tick(t) {
    if (!last) last = t;
    const dt = t - last;
    last = t;

    const w = wrapWidth();

    if (w && !dragging) {
      scrollFloat = wrap(scrollFloat + (speed * dt) / 1000, w);

      // write frequently, but only when it would actually change
      const nextInt = Math.round(scrollFloat);
      if (nextInt !== lastWritten) {
        scroller.scrollLeft = nextInt;
        lastWritten = nextInt;
      }
    }

    requestAnimationFrame(tick);
  }

  requestAnimationFrame(tick);
});
