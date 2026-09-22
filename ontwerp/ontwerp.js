/* Progressieve verbetering: zonder JavaScript blijven alle navigatielinks zichtbaar. */
const header = document.querySelector('.header');
const toggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#navigatie');
if (header && toggle && navigation) {
  function closeMenu(returnFocus = false) {
    navigation.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.querySelector('[data-menu-label]').textContent = 'Menu';
    if (returnFocus) toggle.focus();
  }
  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    navigation.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.querySelector('[data-menu-label]').textContent = open ? 'Sluiten' : 'Menu';
  });
  header.addEventListener('keydown', event => {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      closeMenu(true);
    }
  });
  navigation.addEventListener('click', event => {
    if (event.target.closest('a')) closeMenu();
  });
  const desktop = matchMedia('(min-width: 761px)');
  desktop.addEventListener('change', () => {
    const focusWasInNav = navigation.contains(document.activeElement);
    closeMenu(!desktop.matches && focusWasInNav);
  });
  header.classList.add('menu-ready');
}

// De slider blijft zonder script horizontaal scrollbaar. Geen automatisch afspelen.
for (const slider of document.querySelectorAll('[data-slider]')) {
  const track = slider.querySelector('.moments-track');
  const slides = [...track.querySelectorAll('.moment')];
  const previous = slider.querySelector('.previous');
  const next = slider.querySelector('.next');
  const status = slider.querySelector('.slider-status');
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  let current = 0;
  function positions() {
    return slides.map(slide => slide.offsetLeft - slides[0].offsetLeft);
  }
  function update() {
    const max = track.scrollWidth - track.clientWidth;
    const candidates = positions().map(position => Math.min(position, max));
    current = candidates.reduce((best, position, index) => Math.abs(position - track.scrollLeft) < Math.abs(candidates[best] - track.scrollLeft) ? index : best, 0);
    previous.disabled = track.scrollLeft <= 2;
    next.disabled = track.scrollLeft >= max - 2;
    status.textContent = `${current + 1} / ${slides.length}`;
  }
  function move(direction, event) {
    const index = Math.max(0, Math.min(slides.length - 1, current + direction));
    // Toetsenbord en reduced motion reageren direct. Aanwijzerbeweging is native scroll.
    track.scrollTo({ left:positions()[index], behavior:reduced.matches || event.detail === 0 ? 'instant' : 'smooth' });
  }
  previous.addEventListener('click', event => move(-1, event));
  next.addEventListener('click', event => move(1, event));
  track.addEventListener('scroll', update, { passive:true });
  track.addEventListener('keydown', event => {
    if (event.target !== track || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
    event.preventDefault();
    move(event.key === 'ArrowRight' ? 1 : -1, { detail:0 });
  });
  reduced.addEventListener('change', () => { if (reduced.matches) track.scrollTo({ left:track.scrollLeft, behavior:'instant' }); });
  new ResizeObserver(update).observe(track);
  slider.querySelector('.slider-controls').hidden = false;
  update();
}

// Een korte entree verbindt kaarten binnen een sectie, zonder leestekst te verbergen.
// De basispagina is altijd zichtbaar: ook als deze initialisatie of de observer uitvalt.
(() => {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  if (reduced.matches || !('IntersectionObserver' in window)) return;
  const cards = [...document.querySelectorAll('.content-cards article, .principles article, .service, .help-link, .responsibilities article')];
  const active = new Set();
  const observer = new IntersectionObserver(entries => {
    for (const entry of entries) {
      if (!entry.isIntersecting) continue;
      observer.unobserve(entry.target);
      if (reduced.matches || entry.target.contains(document.activeElement)) continue;
      entry.target.classList.add('is-in');
      active.add(entry.target);
    }
  }, { threshold:0, rootMargin:'0px 0px -24px 0px' });
  function stop() {
    observer.disconnect();
    active.forEach(card => card.classList.remove('is-in'));
    active.clear();
  }
  try {
    for (const card of cards) {
      // Eerste scherm en een rechtstreeks geopende hashbestemming blijven statisch.
      if (card.getBoundingClientRect().top < innerHeight || location.hash) continue;
      card.setAttribute('data-anim', 'op');
      card.addEventListener('animationend', () => {
        card.classList.remove('is-in');
        active.delete(card);
      }, { once:true });
      observer.observe(card);
    }
    document.addEventListener('keydown', stop, { once:true });
    document.addEventListener('focusin', stop, { once:true });
    window.addEventListener('hashchange', stop);
    window.addEventListener('beforeprint', stop);
    reduced.addEventListener('change', event => { if (event.matches) stop(); });
  } catch { stop(); }
})();
