/**
 * SEO/GEO-controle: de dingen die pas opvallen als een site al een tijd live
 * staat. Sitemap die doodloopt, schema dat niet klopt, een paginatype zonder
 * beschrijving.
 *
 * Alleen lezen.
 */

import { patchDnsLookup } from './qa-resolve.mjs';
import { reviewMarkupProblemen } from './review-markup.mjs';
import { kruimelpadProblemen, heeftSearchAction, volgendeSitejobLinks } from './google-schema.mjs';

patchDnsLookup();

const baseUrl = (process.env.SITE_URL || 'http://127.0.0.1:8103').replace(/\/$/, '');
const origin = new URL(baseUrl).origin;
const indexable = process.env.EXPECT_INDEXABLE !== '0';

const meldingen = [];
const meld = (ernst, waar, tekst) => meldingen.push({ ernst, waar, tekst });

async function haal(pad, opties = {}) {
	try {
		return await fetch(origin + pad, { redirect: 'manual', signal: AbortSignal.timeout(30_000), ...opties });
	} catch (error) {
		meld('FOUT', pad, `niet bereikbaar (${error.name})`);
		return null;
	}
}

/* ---- Sitemap en robots -------------------------------------------------- */

const sitemapKort = await haal('/sitemap.xml');
if (sitemapKort && indexable) {
	if (sitemapKort.status === 301 || sitemapKort.status === 302) {
		const doel = sitemapKort.headers.get('location') ?? '';
		if (!doel.includes('sitemap')) meld('FOUT', '/sitemap.xml', `stuurt door naar ${doel}, geen sitemap`);
	} else if (sitemapKort.status !== 200) {
		meld('FOUT', '/sitemap.xml', `HTTP ${sitemapKort.status} — iedereen probeert dit adres eerst`);
	}
}

const sitemap = await fetch(origin + '/wp-sitemap.xml', { redirect: 'follow' }).catch(() => null);
if (!indexable) {
	if (sitemap?.status !== 404) meld('FOUT', '/wp-sitemap.xml', 'staging hoort geen publieke core-sitemap te leveren');
} else if (!sitemap || sitemap.status !== 200) {
	meld('FOUT', '/wp-sitemap.xml', `HTTP ${sitemap?.status ?? 'onbereikbaar'}`);
} else {
	const xml = await sitemap.text();
	const aantal = (xml.match(/<sitemap>|<url>/g) || []).length;
	if (aantal === 0) meld('FOUT', '/wp-sitemap.xml', 'sitemap is leeg');
	else console.log(`Sitemap: ${aantal} ingang(en).`);
}

const robots = await fetch(origin + '/robots.txt', { redirect: 'follow' }).catch(() => null);
if (!robots || robots.status !== 200) {
	meld('FOUT', '/robots.txt', `HTTP ${robots?.status ?? 'onbereikbaar'}`);
} else {
	const tekst = await robots.text();
	if (indexable && !/sitemap:/i.test(tekst)) meld('FOUT', '/robots.txt', 'geen Sitemap-regel');
	if (indexable && /Disallow: \/$/m.test(tekst)) meld('WAARSCHUWING', '/robots.txt', 'de hele site staat op Disallow — bedoeld?');
}

/* ---- Schema per paginatype ---------------------------------------------- */

function schemaUit(html) {
	const grafen = [];
	for (const m of html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/gi)) {
		try {
			grafen.push(JSON.parse(m[1]));
		} catch (error) {
			grafen.push({ __fout: error.message });
		}
	}
	return grafen;
}

function typenUit(grafen) {
	const typen = new Set();
	for (const graaf of grafen) {
		for (const knoop of graaf['@graph'] ?? [graaf]) {
			for (const type of [knoop['@type']].flat()) if (type) typen.add(type);
		}
	}
	return typen;
}

/*
 * Eén pagina per soort. De paden die niet bestaan slaan we over — een verse
 * installatie heeft nog geen artikel, en dat is geen fout in het thema.
 */
const teControleren = [
	// De homepage heeft geen kruimelpad: alleen "Home" is voor Google geen BreadcrumbList.
	{ pad: '/', naam: 'homepage', verwacht: ['Organization', 'WebSite', 'WebPage'] },
	{ pad: '/blog/', naam: 'blogoverzicht', verwacht: ['BreadcrumbList'] },
	{ pad: '/kennisbank/', naam: 'kennisbank', verwacht: ['BreadcrumbList'] },
];

for (const pagina of teControleren) {
	const res = await fetch(origin + pagina.pad, { redirect: 'follow' }).catch(() => null);
	if (!res || res.status === 404) {
		console.log(`Overgeslagen: ${pagina.naam} (${pagina.pad}) bestaat nog niet.`);
		continue;
	}
	if (res.status !== 200) {
		meld('FOUT', pagina.pad, `HTTP ${res.status}`);
		continue;
	}

	const html = await res.text();
	const grafen = schemaUit(html);

	const stuk = grafen.find((g) => g.__fout);
	if (stuk) {
		meld('FOUT', pagina.pad, `JSON-LD is geen geldige JSON: ${stuk.__fout}`);
		continue;
	}
	if (grafen.length === 0) {
		meld('FOUT', pagina.pad, 'geen JSON-LD');
		continue;
	}

	const typen = typenUit(grafen);
	for (const verwacht of pagina.verwacht) {
		if (!typen.has(verwacht)) meld('FOUT', pagina.pad, `${verwacht} ontbreekt in het schema`);
	}

	// Kruimelpad: nooit één ListItem (ook niet op de homepage) en geen verwijzing
	// naar een kruimelpad dat ontbreekt. Een overzicht direct onder Home heeft
	// terecht twee niveaus; een artikel hoort er drie te hebben.
	for (const probleem of kruimelpadProblemen(grafen, { minimum: pagina.minKruimels ?? 2 })) meld('FOUT', pagina.pad, probleem);
	if (heeftSearchAction(grafen)) meld('WAARSCHUWING', pagina.pad, 'SearchAction in de gestructureerde data: Google toont de sitelinks-zoekbalk sinds 21-11-2024 niet meer (plugin?)');
	// Geen review-markup over het eigen bedrijf, ook niet uit een plugin.
	for (const probleem of reviewMarkupProblemen(grafen)) meld('FOUT', pagina.pad, `${probleem} — geen review-markup over het eigen bedrijf; de score hoort alleen zichtbaar op de pagina`);
	// Een volgende link naar sitejob.nl op elke klantsite is link spam ("Gemaakt door SiteJob": nofollow).
	const [volgend] = volgendeSitejobLinks(html);
	if (volgend) meld('FOUT', pagina.pad, `volgende link naar sitejob.nl: ${volgend.slice(0, 80)} — zet rel="nofollow"`);
	for (const graaf of grafen) {
		for (const knoop of graaf['@graph'] ?? [graaf]) {
			if (knoop['@type'] === 'Organization' && JSON.stringify(knoop).includes('[PLACEHOLDER]')) {
				meld('FOUT', pagina.pad, 'er staat een [PLACEHOLDER] in de gestructureerde data');
			}
			if (knoop.aggregateRating && JSON.stringify(knoop.aggregateRating).includes('[PLACEHOLDER]')) {
				meld('FOUT', pagina.pad, 'aggregateRating bevat een placeholder — dat is een verzonnen cijfer aan Google');
			}
		}
	}
}

/* ---- Auteursenumeratie -------------------------------------------------- */

const restUsers = await fetch(origin + '/wp-json/wp/v2/users', { redirect: 'follow' }).catch(() => null);
if (restUsers && restUsers.status === 200) {
	const body = await restUsers.text();
	if (body.trim() !== '[]') {
		meld('FOUT', '/wp-json/wp/v2/users', 'gebruikersnamen zijn openbaar opvraagbaar');
	}
}

const fouten = meldingen.filter((m) => m.ernst === 'FOUT');
const waarschuwingen = meldingen.filter((m) => m.ernst === 'WAARSCHUWING');

console.log('');
for (const m of waarschuwingen) console.log(`WAARSCHUWING  ${m.waar}  ${m.tekst}`);
for (const m of fouten) console.error(`FOUT          ${m.waar}  ${m.tekst}`);
console.log('');
console.log(fouten.length === 0 ? 'SEO-QA: geen fouten.' : `SEO-QA: ${fouten.length} fout(en).`);

process.exit(fouten.length > 0 ? 1 : 0);
