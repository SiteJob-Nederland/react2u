/**
 * Crawlt de publieke site en meldt kapotte links, koppenproblemen en de
 * toegankelijkheidsbasis die een sjabloonfout kapotmaakt.
 *
 * Alleen lezen. Volgt uitsluitend interne links, controleert externe links met
 * HEAD, en verstuurt nooit een formulier.
 */

import { patchDnsLookup } from './qa-resolve.mjs';

patchDnsLookup();

const baseUrl = (process.env.SITE_URL || 'http://127.0.0.1:8103').replace(/\/$/, '');
const origin = new URL(baseUrl).origin;
const MAX_PAGINAS = Number(process.env.QA_MAX_PAGINAS || 60);

/** WordPress-machinerie: geen pagina om te crawlen. */
const GEEN_PAGINA = /^\/(wp-json|wp-admin|wp-login|wp-content|wp-includes|xmlrpc\.php)|\/feed\/?$|\/embed\/?$/;
const GEEN_PAGINA_EXT = /\.(png|jpe?g|webp|avif|svg|ico|css|js|xml|zip|pdf)$/i;

const bezocht = new Map();
const externGecheckt = new Map();
const wachtrij = ['/'];
const meldingen = [];

const meld = (ernst, pagina, tekst) => meldingen.push({ ernst, pagina, tekst });

async function checkExtern(url) {
	if (externGecheckt.has(url)) return externGecheckt.get(url);
	let status;
	try {
		const res = await fetch(url, { method: 'HEAD', redirect: 'follow', signal: AbortSignal.timeout(15_000) });
		status = res.status;
	} catch (error) {
		status = `FOUT: ${error.name}`;
	}
	externGecheckt.set(url, status);
	return status;
}

while (wachtrij.length > 0 && bezocht.size < MAX_PAGINAS) {
	const pad = wachtrij.shift();
	if (bezocht.has(pad)) continue;

	let res;
	try {
		res = await fetch(origin + pad, { redirect: 'follow', signal: AbortSignal.timeout(30_000) });
	} catch (error) {
		bezocht.set(pad, 'onbereikbaar');
		meld('FOUT', pad, `niet bereikbaar (${error.name})`);
		continue;
	}

	const html = await res.text();
	bezocht.set(pad, res.status);

	if (res.status !== 200) {
		meld('FOUT', pad, `HTTP ${res.status}`);
		continue;
	}

	/* ---- Structuur: alleen een sjabloonfout breekt dit ------------------- */

	const h1 = (html.match(/<h1[\s>]/g) || []).length;
	if (h1 !== 1) meld(h1 === 0 ? 'FOUT' : 'WAARSCHUWING', pad, `${h1} h1-elementen (verwacht 1)`);

	if (!/<main[\s>]/.test(html)) meld('FOUT', pad, 'geen <main>-element');
	if (!/<html[^>]+lang="nl/i.test(html)) meld('WAARSCHUWING', pad, 'lang-attribuut is niet nl');

	const beschrijving = html.match(/<meta\s+name="description"\s+content="([^"]*)"/i)?.[1];
	if (!beschrijving) {
		meld('FOUT', pad, 'geen meta-description');
	} else if (beschrijving.length > 160) {
		meld('WAARSCHUWING', pad, `meta-description is ${beschrijving.length} tekens (Google kapt rond 155)`);
	}

	if (!/<link[^>]+rel="canonical"/i.test(html)) meld('FOUT', pad, 'geen canonical');

	const titel = html.match(/<title[^>]*>([^<]*)<\/title>/i)?.[1] ?? '';
	if (titel.trim() === '') meld('FOUT', pad, 'lege paginatitel');

	/*
	 * Kruimelpad. Drie niveaus is de eis voor een artikel — daar hoort het
	 * overzicht tussen Home en het stuk zelf. Een overzichtspagina die zelf
	 * direct onder Home hangt heeft er terecht twee; dat als waarschuwing
	 * melden maakt de uitvoer alleen maar ruis.
	 */
	if (pad !== '/') {
		const isArtikel = /"@type"\s*:\s*"(BlogPosting|Article)"/.test(html);
		const kruimels = (html.match(/<nav class="breadcrumbs"[\s\S]*?<\/nav>/i)?.[0].match(/<li[\s>]/g) || []).length;
		if (kruimels === 0) meld('FOUT', pad, 'geen kruimelpad');
		else if (isArtikel && kruimels < 3) meld('FOUT', pad, `artikel met een kruimelpad van ${kruimels} niveaus (verwacht minimaal 3)`);
		else if (kruimels < 2) meld('WAARSCHUWING', pad, `kruimelpad heeft ${kruimels} niveau`);
	}

	// Afbeeldingen zonder alt-attribuut. Een lege alt="" is een geldige keuze.
	for (const img of html.match(/<img\b[^>]*>/gi) || []) {
		if (!/\balt=/i.test(img)) {
			meld('FOUT', pad, `afbeelding zonder alt: ${img.slice(0, 90)}`);
		}
	}

	/* ---- Links ----------------------------------------------------------- */

	for (const match of html.matchAll(/<a\b[^>]*href="([^"]+)"/gi)) {
		const href = match[1];
		if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) continue;

		let url;
		try {
			url = new URL(href, origin + pad);
		} catch {
			meld('FOUT', pad, `onbruikbare link: ${href}`);
			continue;
		}

		if (url.origin !== origin) {
			const status = await checkExtern(url.href);
			if (typeof status === 'number' ? status >= 400 : true) {
				meld('WAARSCHUWING', pad, `externe link ${url.href} → ${status}`);
			}
			continue;
		}

		const doel = url.pathname;
		if (GEEN_PAGINA.test(doel) || GEEN_PAGINA_EXT.test(doel)) continue;
		if (!bezocht.has(doel) && !wachtrij.includes(doel)) wachtrij.push(doel);
	}
}

const fouten = meldingen.filter((m) => m.ernst === 'FOUT');
const waarschuwingen = meldingen.filter((m) => m.ernst === 'WAARSCHUWING');

console.log(`Gecrawld: ${bezocht.size} pagina's op ${origin}`);
console.log('');
for (const m of waarschuwingen) console.log(`WAARSCHUWING  ${m.pagina}  ${m.tekst}`);
for (const m of fouten) console.error(`FOUT          ${m.pagina}  ${m.tekst}`);
console.log('');
console.log(fouten.length === 0 ? 'Link-QA: geen fouten.' : `Link-QA: ${fouten.length} fout(en).`);

if (wachtrij.length > 0) {
	console.log(`Let op: ${wachtrij.length} pagina's niet bezocht (limiet ${MAX_PAGINAS}). Verhoog QA_MAX_PAGINAS om verder te gaan.`);
}

process.exit(fouten.length > 0 ? 1 : 0);
