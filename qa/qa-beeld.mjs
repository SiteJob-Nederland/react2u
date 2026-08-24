/**
 * Schermafdrukken van elk paginatype, op desktop en mobiel.
 *
 * Geen goed/fout-oordeel: dit script maakt de afdrukken zodat je ze zelf kunt
 * bekijken, en meet er meteen de dingen bij die je op een afdruk niet ziet
 * (aantal H1's, canonical, horizontale overflow, JS-fouten).
 *
 * Eén ding is bewust ingebouwd. Het thema onthult secties met een waarnemer,
 * plus een vangnet dat elke twee seconden draait. Een volledige schermafdruk
 * scrollt niet, dus zonder dat scrollen komt alles onder de vouw er blanco uit
 * — en dan denk je dat de halve pagina stuk is. Daarom scrollt dit script eerst
 * naar beneden, wacht daar op het vangnet, en gaat pas dan terug naar boven.
 *
 * Draaien:  node qa-beeld.mjs [map]     (standaard: ./schermafdrukken)
 *           SITE_URL=https://staging... node qa-beeld.mjs
 */

import { chromium } from 'playwright';
import { browserArgs } from './qa-resolve.mjs';
import { mkdir } from 'node:fs/promises';

const base = (process.env.SITE_URL || 'http://127.0.0.1:8101').replace(/\/$/, '');
const out = process.argv[2] || './schermafdrukken';
await mkdir(out, { recursive: true });

/*
 * PER KLANT: zet hier de paden van déze site neer. Wat er nu staat zijn de
 * demo-paden uit deploy/seed.php; zodra er echte inhoud is, vervang je ze —
 * anders maakt dit script negen keer een afdruk van een 404.
 */
const paginas = [
	['home', '/', 1440],
	['home-mobiel', '/', 390],
	['dienst', '/verzuimbegeleiding-wvp/', 1440],
	['diensten-overzicht', '/diensten/', 1440],
	['kennisartikel', '/kennisbank/demo-kennisartikel/', 1440],
	['blogartikel', '/demo-blogartikel/', 1440],
	['auteur', '/author/redactie/', 1440],
	['kennisbank', '/kennisbank/', 1440],
	['fout404', '/bestaat-niet-xyz/', 1440],
];

const browser = await chromium.launch({ args: browserArgs() });
let fouten = 0;

for (const [naam, pad, breedte] of paginas) {
	const ctx = await browser.newContext({ viewport: { width: breedte, height: 1000 }, colorScheme: 'light' });
	const page = await ctx.newPage();

	const jsFouten = [];
	page.on('pageerror', (e) => jsFouten.push(e.message));

	const res = await page.goto(base + pad, { waitUntil: 'networkidle' });
	await page.addStyleTag({ content: 'html{scroll-behavior:auto !important}' });

	await page.evaluate(async () => {
		const stap = window.innerHeight * 0.8;
		for (let y = 0; y < document.body.scrollHeight; y += stap) {
			window.scrollTo(0, y);
			await new Promise((r) => setTimeout(r, 220));
		}
		window.scrollTo(0, document.body.scrollHeight);
		await new Promise((r) => setTimeout(r, 2600)); // vangnet van site.js
		window.scrollTo(0, 0);
		await new Promise((r) => setTimeout(r, 400));
	});

	const m = await page.evaluate(() => ({
		overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
		h1: document.querySelectorAll('h1').length,
		main: document.querySelectorAll('main#main').length,
		canonical: !!document.querySelector('link[rel=canonical]'),
		beschrijving: !!document.querySelector('meta[name=description]'),
		verborgen: [...document.querySelectorAll('[data-anim]')].filter((el) => getComputedStyle(el).opacity === '0').length,
	}));

	/*
	 * Nu nog een keer, met beweging uit.
	 *
	 * De secties komen binnen met scroll-gestuurde animaties (zie style.css
	 * blok 16.17). In de stand "beweging uit" hoort daar niets van over te
	 * blijven: geen clip-path, geen doorzichtig element. Blijft er wel iets
	 * staan, dan is er inhoud die iemand met beweging uit nooit te zien krijgt,
	 * en dat is de ergste afloop van dit soort effecten.
	 */
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.waitForTimeout(250);
	const stil = await page.evaluate(() => {
		/*
		 * Alleen de elementen die in blok 16.17 een scroll-animatie krijgen.
		 * Niet alles wat een clip-path heeft: .sr-only gebruikt er ook een, en
		 * die hoort juist onzichtbaar te zijn.
		 */
		/* PER KLANT: de selectors die in style.css blok 16 een scroll-animatie
		   krijgen. Leeg laten mag; dan controleert dit stuk niets. */
		const doelen = [];
		const blijft = [];
		for (const sel of doelen) {
			for (const el of document.querySelectorAll(sel)) {
				const cs = getComputedStyle(el);
				const geklipt = cs.clipPath !== 'none' && !/^inset\(0(px)?( 0(px|%)?){0,3}\)$/.test(cs.clipPath.trim());
				if (geklipt || cs.opacity === '0' || cs.visibility === 'hidden') {
					blijft.push(`${sel} (${cs.clipPath}, opacity ${cs.opacity})`);
				}
			}
		}
		return blijft;
	});

	const mis = [];
	if (m.h1 !== 1) mis.push(`${m.h1} H1`);
	if (m.main !== 1) mis.push(`${m.main} <main id="main">`);
	if (m.overflow !== 0) mis.push(`${m.overflow}px horizontale overflow`);
	if (!m.canonical) mis.push('geen canonical');
	if (!m.beschrijving) mis.push('geen meta-description');
	if (m.verborgen > 0) mis.push(`${m.verborgen} secties nog verborgen`);
	if (stil.length) mis.push(`met beweging uit blijft geklipt: ${stil.join(', ')}`);
	if (jsFouten.length) mis.push(`JS: ${jsFouten.join(' | ')}`);

	/*
	 * En dan pas de afdruk — nog steeds met beweging uit.
	 *
	 * Wat een scroll-gestuurde animatie toont, hangt af van de scrollpositie op
	 * dat moment, en een volledige schermafdruk staat per definitie op één
	 * positie. Zonder dit komt de halve pagina er half weggeklipt uit en ga je
	 * een fout zoeken die er niet is. Met beweging uit staat alles in zijn
	 * eindstand, en dat is precies wat je wilt beoordelen.
	 */
	await page.screenshot({ path: `${out}/${naam}.png`, fullPage: true });

	if (mis.length) {
		fouten += 1;
		console.error(`FOUT  ${naam.padEnd(15)} ${res.status()}  ${mis.join('; ')}`);
	} else {
		console.log(`ok    ${naam.padEnd(15)} ${res.status()}`);
	}

	await ctx.close();
}

await browser.close();

console.log('');
console.log(fouten === 0 ? `Beeld-QA: geen fouten. Afdrukken staan in ${out}/` : `Beeld-QA: ${fouten} pagina('s) met een probleem.`);
process.exit(fouten > 0 ? 1 : 0);
