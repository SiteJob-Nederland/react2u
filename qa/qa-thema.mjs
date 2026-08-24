/**
 * Rendering-QA met een echte browser.
 *
 * Controleert de dingen die een sjabloonfout veroorzaakt en die je op één
 * schermbreedte niet ziet: navigatie die op een tussenmaat onbereikbaar wordt,
 * horizontale overflow, een tweede H1, consolefouten.
 *
 * Alleen lezen. Er wordt niets ingevuld en niets verstuurd.
 */
import { chromium } from 'playwright';
import { browserArgs } from './qa-resolve.mjs';
import { mkdir } from 'node:fs/promises';

const baseUrl = (process.env.SITE_URL || 'http://127.0.0.1:8103/').replace(/\/$/, '') + '/';
const uit = 'uitvoer';

await mkdir(uit, { recursive: true });

const browser = await chromium.launch({ headless: true, args: browserArgs() });
const consoleErrors = [];
const fouten = [];
const waarschuwingen = [];

const meld = (lijst, tekst) => lijst.push(tekst);

async function inspecteer(viewport, bestand, openMenu = false) {
	const page = await browser.newPage({ viewport });
	page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });
	page.on('pageerror', (e) => consoleErrors.push(e.message));

	await page.goto(baseUrl, { waitUntil: 'load', timeout: 120_000 });

	/*
	 * Rustig doorscrollen voordat we een afdruk maken. De onthul-animatie zet
	 * elementen pas zichtbaar zodra ze in beeld komen; zonder scrollen levert
	 * een fullPage-afdruk een pagina vol lege vlakken op, en dan meet je het
	 * effect in plaats van de pagina.
	 */
	await page.evaluate(async () => {
		const stap = Math.round(window.innerHeight * 0.7);
		for (let y = 0; y < document.body.scrollHeight; y += stap) {
			window.scrollTo(0, y);
			await new Promise((r) => setTimeout(r, 250));
		}
		window.scrollTo(0, 0);
		await new Promise((r) => setTimeout(r, 400));
	});

	// Wat hierna nog verborgen is, hoort de QA te melden.
	const verborgen = await page.evaluate(() => document.querySelectorAll('[data-anim]:not(.is-in)').length);

	if (openMenu) {
		const knop = page.locator('.menu-button');
		if (await knop.isVisible()) await knop.click();
	}

	const resultaat = await page.evaluate(() => ({
		title: document.title,
		titleLengte: document.title.length,
		h1: document.querySelectorAll('h1').length,
		main: document.querySelectorAll('main#main').length,
		skipLink: !!document.querySelector('.skip-link'),
		bodyBreedte: document.body.scrollWidth,
		vensterBreedte: document.documentElement.clientWidth,
		beschrijving: document.querySelector('meta[name="description"]')?.content ?? null,
		canonical: document.querySelector('link[rel="canonical"]')?.href ?? null,
		schema: document.querySelectorAll('script[type="application/ld+json"]').length,
		placeholders: document.querySelectorAll('.is-placeholder').length,
	}));

	await page.screenshot({ path: `${uit}/${bestand}`, fullPage: true });
	await page.close();
	return { ...resultaat, verborgen };
}

const desktop = await inspecteer({ width: 1440, height: 900 }, 'home-desktop.png');
const mobiel = await inspecteer({ width: 390, height: 844 }, 'home-mobiel-menu.png', true);

for (const [naam, r] of [['desktop', desktop], ['mobiel', mobiel]]) {
	if (r.h1 !== 1) meld(fouten, `${naam}: ${r.h1} h1-elementen (verwacht precies 1)`);
	if (r.main !== 1) meld(fouten, `${naam}: geen <main id="main">`);
	if (!r.skipLink) meld(fouten, `${naam}: geen skip-link naar de inhoud`);
	// Eén pixel speling: subpixel-afronding op een schaalfactor telt niet als overflow.
	if (r.bodyBreedte > r.vensterBreedte + 1) {
		meld(fouten, `${naam}: horizontale overflow (${r.bodyBreedte}px in ${r.vensterBreedte}px)`);
	}
	if (!r.beschrijving) meld(fouten, `${naam}: geen meta-description`);
	if (!r.canonical) meld(fouten, `${naam}: geen canonical`);
	if (r.schema === 0) meld(fouten, `${naam}: geen JSON-LD`);
	if (r.verborgen > 0) meld(waarschuwingen, `${naam}: ${r.verborgen} element(en) nog onzichtbaar na doorscrollen — controleer de onthul-animatie`);
	if (r.titleLengte > 62) meld(waarschuwingen, `${naam}: paginatitel is ${r.titleLengte} tekens (Google kapt rond 60)`);
	if (r.placeholders > 0) meld(waarschuwingen, `${naam}: ${r.placeholders} zichtbare [PLACEHOLDER]-markeringen`);
}

/*
 * Breekpuntsweep. De klassieke fout: de balknavigatie verdwijnt op een andere
 * breedte dan waarop de burger verschijnt, waardoor het menu op een tussenmaat
 * onbereikbaar is. Dat merk je niet als je alleen 1440 en 390 test.
 */
const zichtbaar = (page, selector) => page.evaluate((doel) => {
	const el = document.querySelector(doel);
	if (!el) return false;
	const box = el.getBoundingClientRect();
	return getComputedStyle(el).display !== 'none' && box.width > 0 && box.height > 0;
}, selector);

const breedtes = [1440, 1280, 1180, 1101, 1100, 1025, 1024, 900, 830, 768, 600, 390, 320];
const navigatie = [];

for (const width of breedtes) {
	const page = await browser.newPage({ viewport: { width, height: 800 } });
	await page.goto(baseUrl, { waitUntil: 'load', timeout: 120_000 });

	const burger = await zichtbaar(page, '.menu-button');
	if (burger) {
		await page.click('.menu-button');
		await page.waitForTimeout(150);
	}

	const menuBereikbaar = await zichtbaar(page, '.main-nav a');
	const ctaBereikbaar = await zichtbaar(page, '.header-actions .button');

	let escapeSluit = null;
	if (burger) {
		await page.keyboard.press('Escape');
		await page.waitForTimeout(150);
		escapeSluit = !(await zichtbaar(page, '[data-nav-panel] a'));
	}

	const overflow = await page.evaluate(() => document.body.scrollWidth - document.documentElement.clientWidth);

	navigatie.push({ width, burger, menuBereikbaar, ctaBereikbaar, escapeSluit, overflow });
	await page.close();
}

await browser.close();

for (const rij of navigatie) {
	if (!rij.menuBereikbaar) meld(fouten, `navigatie onbereikbaar op ${rij.width}px`);
	if (!rij.ctaBereikbaar) meld(fouten, `hoofd-CTA onbereikbaar op ${rij.width}px`);
	if (rij.escapeSluit === false) meld(fouten, `Escape sluit het menu niet op ${rij.width}px`);
	if (rij.overflow > 1) meld(fouten, `horizontale overflow van ${rij.overflow}px op ${rij.width}px`);
}

for (const fout of consoleErrors) meld(fouten, `consolefout: ${fout}`);

console.log(JSON.stringify({ desktop, mobiel, navigatie }, null, 2));
console.log('');
for (const w of waarschuwingen) console.log(`WAARSCHUWING  ${w}`);
for (const f of fouten) console.error(`FOUT          ${f}`);
console.log('');
console.log(fouten.length === 0 ? 'Thema-QA: geen fouten.' : `Thema-QA: ${fouten.length} fout(en).`);

process.exit(fouten.length > 0 ? 1 : 0);
