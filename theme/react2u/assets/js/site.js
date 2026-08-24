/**
 * React2u — gedrag aan de voorkant.
 *
 * Alles hieronder is een verbetering bovenop iets dat zonder JavaScript al
 * werkt: het menu is een lijst links, de inhoudsopgave en de FAQ zijn
 * <details>, en de CTA's zijn gewone links. Valt dit bestand weg, dan blijft de
 * pagina bruikbaar.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	var CTA_KEY = 'react2u-cta-dismissed';

	/* ---- Sitehoofd: schaduw zodra je scrolt ------------------------------ */

	var header = document.querySelector('[data-header]');

	if (header) {
		var updateHeader = function () {
			header.classList.toggle('scrolled', window.scrollY > 16);
		};
		updateHeader();
		window.addEventListener('scroll', updateHeader, { passive: true });
	}

	/* ---- Mobiel menu ------------------------------------------------------
	 * Het paneel bevat de navigatie én de hoofd-CTA. Sluiten moet op elke
	 * manier kunnen die iemand verwacht: opnieuw op de knop, Escape, of ergens
	 * anders klikken. Zonder die routes zit een toetsenbordgebruiker vast.
	 */

	var menuButton = document.querySelector('.menu-button');
	var navPanel = document.querySelector('[data-nav-panel]');

	var menuIsOpen = function () {
		return menuButton && menuButton.getAttribute('aria-expanded') === 'true';
	};

	var setMenu = function (open) {
		if (!menuButton || !navPanel) {
			return;
		}
		menuButton.setAttribute('aria-expanded', String(open));
		navPanel.classList.toggle('open', open);
		document.body.classList.toggle('menu-open', open);
	};

	if (menuButton && navPanel) {
		menuButton.addEventListener('click', function (event) {
			event.stopPropagation();
			setMenu(!menuIsOpen());
		});

		navPanel.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				setMenu(false);
			});
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && menuIsOpen()) {
				setMenu(false);
				menuButton.focus();
			}
		});

		document.addEventListener('click', function (event) {
			if (menuIsOpen() && !navPanel.contains(event.target) && event.target !== menuButton) {
				setMenu(false);
			}
		});

		// Terug naar de bureaubladindeling laat de klasse anders achter op een
		// element dat weer gewoon een rij is.
		window.addEventListener('resize', function () {
			if (menuIsOpen() && window.matchMedia('(min-width: 1025px)').matches) {
				setMenu(false);
			}
		});
	}

	/* ---- Inhoudsopgave ----------------------------------------------------
	 * Open op desktop, dicht op smalle schermen — daar zou hij anders het halve
	 * scherm vullen voordat het artikel begint.
	 */

	var toc = document.querySelector('[data-toc]');

	if (toc && window.matchMedia('(max-width: 1024px)').matches) {
		toc.removeAttribute('open');
	}

	/* ---- Sticky CTA -------------------------------------------------------
	 * Pas tonen zodra de lezer voorbij de kop is, en weer verbergen zodra de
	 * afsluitende CTA of de footer in beeld komt: twee keer dezelfde vraag
	 * tegelijk oogt slordig. Wie hem wegklikt, ziet hem dit bezoek niet meer.
	 */

	var stickyCta = document.querySelector('[data-sticky-cta]');
	var stickyTrigger = document.querySelector('.resource-header');

	var stickyDismissed = function () {
		try {
			return sessionStorage.getItem(CTA_KEY) === '1';
		} catch (e) {
			return false;
		}
	};

	if (stickyCta && stickyTrigger && 'IntersectionObserver' in window && !stickyDismissed()) {
		stickyCta.removeAttribute('hidden');

		var suppressors = document.querySelectorAll('.resource-final-cta, .site-footer, .resource-faq');
		var visible = new Set();
		var passedIntro = false;

		var syncSticky = function () {
			stickyCta.classList.toggle('is-visible', passedIntro && visible.size === 0);
		};

		new IntersectionObserver(
			function (entries) {
				passedIntro = !entries[0].isIntersecting;
				syncSticky();
			},
			{ rootMargin: '-80px 0px 0px 0px' }
		).observe(stickyTrigger);

		var suppressionObserver = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					visible.add(entry.target);
				} else {
					visible.delete(entry.target);
				}
			});
			syncSticky();
		});

		suppressors.forEach(function (element) {
			suppressionObserver.observe(element);
		});

		var closeButton = stickyCta.querySelector('[data-sticky-close]');
		if (closeButton) {
			closeButton.addEventListener('click', function () {
				stickyCta.classList.remove('is-visible');
				stickyCta.setAttribute('hidden', '');
				try {
					sessionStorage.setItem(CTA_KEY, '1');
				} catch (e) {
					/* Niets aan te doen; de balk blijft dan weg tot een herlaadbeurt. */
				}
			});
		}
	}

	/* ---- Cijfers die meetellen --------------------------------------------
	 * Alleen bij een waarde die uit één getal bestaat, met eventueel iets ervoor
	 * of erna (120+, 98%, € 1,95). Staat er nog een placeholder in, dan zit er
	 * markup omheen en laten we het cijfer met rust.
	 */

	var counted = new WeakSet();

	var countUp = function (scope) {
		if (reduceMotion.matches) {
			return;
		}

		var targets = scope.matches('.stat, .case-card')
			? scope.querySelectorAll('.stat-value, .case-metric-value')
			: [];

		targets.forEach(function (element) {
			if (counted.has(element) || element.children.length) {
				return;
			}

			var original = element.textContent.trim();
			var match = original.match(/^(\D*?)([\d.]+(?:,\d+)?)(\D*)$/);
			if (!match) {
				return;
			}

			var prefix = match[1];
			var raw = match[2];
			var suffix = match[3];
			var decimals = raw.indexOf(',') === -1 ? 0 : raw.split(',')[1].length;
			var target = parseFloat(raw.replace(/\./g, '').replace(',', '.'));

			if (!isFinite(target) || target === 0) {
				return;
			}

			counted.add(element);

			var format = function (value) {
				return value.toLocaleString('nl-NL', {
					minimumFractionDigits: decimals,
					maximumFractionDigits: decimals
				});
			};

			var duration = 1100;
			var started = null;

			var step = function (now) {
				if (started === null) {
					started = now;
				}
				var progress = Math.min((now - started) / duration, 1);
				// easeOutExpo: snel op gang, rustig uitlopend.
				var eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
				element.textContent = prefix + format(target * eased) + suffix;

				if (progress < 1) {
					requestAnimationFrame(step);
				} else {
					element.textContent = original;
				}
			};

			requestAnimationFrame(step);
		});
	};

	/* ---- Onthullen bij het scrollen ---------------------------------------
	 * De attributen worden hier gezet, niet in de HTML: zonder JavaScript staat
	 * er dan nooit iets onzichtbaar op de pagina. Wat al in beeld staat slaan we
	 * over — dat eerst laten wegvallen om het daarna terug te laten komen is
	 * precies het soort effect waar niemand op zit te wachten.
	 */

	var reveal = function () {
		if (reduceMotion.matches || !('IntersectionObserver' in window)) {
			return;
		}

		var groups = [
			/*
			 * De kop wordt met clip-path onthuld. Een element dat volledig
			 * weggeklipt is, telt voor de waarnemer als nul pixels in beeld en
			 * zou dus nooit binnenkomen — daarom kijken we hier naar de sectie
			 * eromheen en onthullen we de kop zelf.
			 */
			{ selector: '.section-heading', anim: 'mask', stagger: 0, watch: 'section' },
			{ selector: '.service-card, .case-card, .step, .card', anim: 'rise', stagger: 70 },
			{ selector: '.stat', anim: 'rise', stagger: 60 },
			{ selector: '.review.size-feature, .cta-section, .logo-row, .banner, .toc, .author-card, .faq-item', anim: 'up', stagger: 40 },
			{ selector: '.entry-content > h2, .entry-content > .cta-inline', anim: 'up', stagger: 0 }
		];

		var revealMap = new WeakMap();

		var show = function (element) {
			element.classList.add('is-in');
			countUp(element);
		};

		var observer = new IntersectionObserver(
			function (entries, self) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					self.unobserve(entry.target);
					(revealMap.get(entry.target) || [entry.target]).forEach(show);
				});
			},
			{ rootMargin: '0px 0px -6% 0px', threshold: 0.12 }
		);

		var fold = window.innerHeight * 0.92;

		/*
		 * Eerst de elementen die hun bedoeling al in het sjabloon meekregen:
		 * data-anim-op. Die krijgen hier pas een echte data-anim.
		 *
		 * De vouwgrens hieronder geldt voor deze groep bewust niet: een sjabloon
		 * dat zelf om beweging vraagt, vraagt er meestal om juist bij het laden.
		 * Richting en vertraging staan al in de opmaak, dus er komt hier geen
		 * groepsstagger overheen.
		 */
		document.querySelectorAll('[data-anim-op]').forEach(function (element) {
			element.setAttribute('data-anim', element.getAttribute('data-anim-op'));
			element.removeAttribute('data-anim-op');
			revealMap.set(element, [element]);
			observer.observe(element);
		});

		groups.forEach(function (group) {
			var items = document.querySelectorAll(group.selector);
			var index = 0;

			items.forEach(function (element) {
				if (element.hasAttribute('data-anim') || element.getBoundingClientRect().top < fold) {
					countUp(element);
					return;
				}
				element.setAttribute('data-anim', group.anim);
				if (group.stagger) {
					element.style.setProperty('--anim-delay', (index % 4) * group.stagger + 'ms');
				}
				index += 1;

				var watched = group.watch ? element.closest(group.watch) || element : element;
				var queue = revealMap.get(watched) || [];
				queue.push(element);
				revealMap.set(watched, queue);
				observer.observe(watched);
			});
		});

		/*
		 * Vangnet. Onzichtbare inhoud is de ergste afloop van dit effect, dus
		 * wat na twee seconden in beeld staat en nog niet onthuld is, tonen we
		 * alsnog — ongeacht wat de waarnemer ervan vond.
		 */
		var alles = function () {
			document.querySelectorAll('[data-anim]:not(.is-in)').forEach(show);
		};

		window.setInterval(function () {
			document.querySelectorAll('[data-anim]:not(.is-in)').forEach(function (element) {
				if (element.getBoundingClientRect().top < window.innerHeight) {
					show(element);
				}
			});
		}, 2000);

		/*
		 * En een harde ondergrens: alles wat een minuut na het laden nog verborgen
		 * is, tonen we hoe dan ook. Dat kost hooguit een animatie die iemand niet
		 * heeft gezien; het alternatief is inhoud die er voor een schermlezer,
		 * een schermafdruk of een trage waarnemer nooit komt.
		 */
		window.setTimeout(alles, 60000);
		window.addEventListener('beforeprint', alles);
	};

	reveal();

	/* ---- Leesvoortgang -----------------------------------------------------
	 * Alleen op een artikel, en alleen over de tekst zelf: een balk die ook de
	 * footer meetelt, staat op negentig procent terwijl je nog middenin zit.
	 */

	var article = document.querySelector('.resource-main');

	if (article && !reduceMotion.matches) {
		var bar = document.createElement('div');
		bar.className = 'reading-progress';
		bar.setAttribute('aria-hidden', 'true');
		bar.innerHTML = '<span></span>';
		document.body.appendChild(bar);

		var fill = bar.firstElementChild;

		var updateProgress = function () {
			var box = article.getBoundingClientRect();
			var total = box.height - window.innerHeight;
			var progress = total > 0 ? -box.top / total : 0;
			fill.style.setProperty('--progress', Math.min(Math.max(progress, 0), 1).toFixed(3));
		};

		updateProgress();
		window.addEventListener('scroll', updateProgress, { passive: true });
		window.addEventListener('resize', updateProgress);
	}

	/* ---- Inhoudsopgave loopt mee ------------------------------------------ */

	var tocLinks = document.querySelectorAll('.toc-list a');

	if (tocLinks.length && 'IntersectionObserver' in window) {
		var headings = [];
		tocLinks.forEach(function (link) {
			var heading = document.getElementById(decodeURIComponent(link.hash.slice(1)));
			if (heading) {
				headings.push({ heading: heading, link: link });
			}
		});

		var setCurrent = function (link) {
			tocLinks.forEach(function (item) {
				item.classList.toggle('is-current', item === link);
			});
		};

		var seen = new Map();

		var tocObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					seen.set(entry.target, entry.isIntersecting);
				});

				var active = headings.filter(function (item) {
					return seen.get(item.heading);
				})[0];

				if (active) {
					setCurrent(active.link);
				}
			},
			{ rootMargin: '-100px 0px -65% 0px' }
		);

		headings.forEach(function (item) {
			tocObserver.observe(item.heading);
		});
	}
})();
