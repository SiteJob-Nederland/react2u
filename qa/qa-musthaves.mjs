/**
 * Must-haves per paginatype (docs/must-haves.md).
 *
 * Controleert of elk paginatype de vaste onderdelen heeft: auteur + datum +
 * banner op artikelen, een volwaardige auteurspagina, en meerdere CTA's op een
 * servicepagina. Alleen lezen.
 *
 * De pagina's worden OPGEZOCHT via de sitemap, niet uit een vaste lijst met
 * demo-slugs. Dat was eerder wel zo, en dat ging stil mis: zodra een site echte
 * inhoud kreeg in plaats van de demo-inhoud, gaf dit script "geen fouten" —
 * niet omdat alles klopte, maar omdat het niets meer vond om te controleren.
 * Een controle die stilvalt is erger dan een controle die klaagt.
 *
 * Overschrijven kan met omgevingsvariabelen, bijvoorbeeld:
 *   MH_ARTIKEL=/mijn-artikel/ MH_DIENST=/diensten/iets/ npm run musthaves
 */

import { patchDnsLookup } from './qa-resolve.mjs';

patchDnsLookup();

const baseUrl = (process.env.SITE_URL || 'http://127.0.0.1:8101').replace(/\/$/, '');
const meldingen = [];
const meld = (ernst, waar, tekst) => meldingen.push({ ernst, waar, tekst });

async function haal(pad) {
	try {
		const res = await fetch(baseUrl + pad, { redirect: 'follow', signal: AbortSignal.timeout(30_000) });
		return { status: res.status, html: res.status === 200 ? await res.text() : '' };
	} catch (error) {
		return { status: `FOUT: ${error.name}`, html: '' };
	}
}

const telH1 = (html) => (html.match(/<h1[\s>]/g) || []).length;
const heeft = (html, re) => re.test(html);

/* ---- Pagina's opzoeken via de sitemap ----------------------------------- */

async function sitemapPaden() {
	const index = await haal('/sitemap.xml');
	if (index.status !== 200) return [];

	const kaarten = [...index.html.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1]);
	const paden = [];

	for (const kaart of kaarten) {
		const pad = kaart.replace(baseUrl, '');
		if (!/sitemap/i.test(pad)) {
			paden.push(pad);
			continue;
		}
		const sub = await haal(pad);
		if (sub.status !== 200) continue;
		for (const m of sub.html.matchAll(/<loc>([^<]+)<\/loc>/g)) {
			paden.push(m[1].replace(baseUrl, ''));
		}
	}

	return [...new Set(paden)];
}

const alle = await sitemapPaden();
if (alle.length === 0 && process.env.EXPECT_INDEXABLE !== '0') {
	meld('WAARSCHUWING', '/sitemap.xml', 'geen sitemap gevonden');
}

/**
 * Eerste artikel uit een overzicht. Betrouwbaarder dan gokken op de vorm van
 * een URL: op de meeste sites zien /contact/ en /een-blogartikel/ er precies
 * hetzelfde uit, en dan controleer je straks de contactpagina op een byline.
 *
 * De kaarten in de overzichten dragen class="card-link"; daar pikken we de
 * eerste uit.
 */
async function eersteUitOverzicht(overzicht) {
	const { status, html } = await haal(overzicht);
	if (status !== 200) return null;

	const href = html.match(/<a class="card-link"[^>]*href="([^"]+)"/i)?.[1] ?? null;

	return href ? href.replace(baseUrl, '') : null;
}

/** Eerste pad dat aan een test voldoet en géén overzichtspagina is. */
function zoek(test, uitsluiten = []) {
	return alle.find((pad) => test(pad) && !uitsluiten.includes(pad)) ?? null;
}

const blogPad = process.env.MH_BLOGOVERZICHT ?? '/blog/';

const artikelPad = process.env.MH_ARTIKEL ?? await eersteUitOverzicht(blogPad);
const kennisPad  = process.env.MH_KENNIS
	?? await eersteUitOverzicht('/kennisbank/')
	?? zoek((p) => /^\/kennisbank\/.+\/$/.test(p), ['/kennisbank/']);

const dienstKandidaten = [
	'/verzuimbegeleiding-wvp/', '/verzuimbegeleiding-erd-zw/',
	'/preventie-en-vitaliteit/', '/begeleiding-en-coaching/',
	'/trainingen-en-workshops/', '/risicomanagement/',
];
let dienstPad = process.env.MH_DIENST
	?? zoek((p) => /^\/diensten\/.+\/$/.test(p) || dienstKandidaten.includes(p), ['/diensten/']);
if (!dienstPad) {
	for (const pad of dienstKandidaten) {
		if ((await haal(pad)).status === 200) { dienstPad = pad; break; }
	}
}

console.log(`Gecontroleerd: artikel=${artikelPad ?? '—'}  kennisbank=${kennisPad ?? '—'}  dienst=${dienstPad ?? '—'}`);

/* ---- Artikel (blog én kennisbank, zelfde eisen) ------------------------- */

for (const [naam, pad] of [
	['blogartikel', artikelPad],
	['kennisartikel', kennisPad],
]) {
	if (!pad) {
		meld('FOUT', '(geen)', `geen ${naam} gevonden om te controleren — staat er inhoud op de site?`);
		continue;
	}

	const { status, html } = await haal(pad);
	if (status !== 200) {
		meld('FOUT', pad, `${naam} niet op te halen (${status})`);
		continue;
	}

	if (telH1(html) !== 1) meld('FOUT', pad, `${naam}: ${telH1(html)} H1 (verwacht 1)`);

	// Auteur bovenaan mét link naar de auteurspagina.
	const byline = html.match(/<div class="byline">[\s\S]*?<\/div>\s*(?=<\/header>|<div class="shell resource-banner")/i)?.[0]
		?? html.match(/<div class="byline">[\s\S]{0,1200}/i)?.[0] ?? '';
	if (!/class="byline-author"[^>]*href="[^"]*author/i.test(html) && !/rel="author"/i.test(html)) {
		meld('FOUT', pad, `${naam}: geen auteur met link naar de auteurspagina bovenaan`);
	}

	// Publicatiedatum.
	if (!heeft(html, /<time[^>]+datetime="\d{4}-\d{2}-\d{2}/i)) {
		meld('FOUT', pad, `${naam}: geen publicatiedatum`);
	}

	// Horizontale banner, breed en dun. Er hoort altijd een bannerslot te staan;
	// met een echte foto controleren we of de verhouding klopt, met de terugval
	// (beeldmerk) melden we dat er nog beeld ontbreekt.
	const bannerFig = html.match(/<figure class="banner[^"]*"[\s\S]*?<\/figure>/i)?.[0] ?? '';
	const bannerFallback = /<div class="banner is-fallback"/i.test(html);
	if (bannerFig) {
		const w = Number(bannerFig.match(/width="(\d+)"/)?.[1] ?? 0);
		const h = Number(bannerFig.match(/height="(\d+)"/)?.[1] ?? 0);
		if (w && h && w / h < 2.4) {
			meld('FOUT', pad, `banner-verhouding ${w}×${h} is niet "breed en dun" (moet ≥ 2,4:1)`);
		}
	} else if (bannerFallback) {
		meld('WAARSCHUWING', pad, `${naam}: banner valt terug op het beeldmerk — nog geen uitgelichte afbeelding`);
	} else {
		meld('FOUT', pad, `${naam}: geen bannerslot in het sjabloon`);
	}

	// Banner mag de vouw niet opeten: hij staat ná de H1, niet ervoor.
	const h1Pos = html.search(/<h1[\s>]/i);
	const bannerPos = html.search(/class="[^"]*banner[^"]*"/i);
	if (bannerPos !== -1 && h1Pos !== -1 && bannerPos < h1Pos) {
		meld('FOUT', pad, `${naam}: de banner staat vóór de H1 en neemt de ruimte boven de vouw`);
	}
}

/* ---- Auteurspagina ------------------------------------------------------ */

/* De auteurspagina uit het artikel halen in plaats van uit een vaste slug: op
   een echte site heet de auteur zelden "redactie". */
let auteurPad = process.env.MH_AUTEUR ?? null;
if (!auteurPad && artikelPad) {
	const bron = await haal(artikelPad);
	auteurPad = bron.html.match(/href="[^"]*?(\/author\/[^"\/]+\/)"/i)?.[1] ?? null;
}
if (auteurPad) {
	const auteur = await haal(auteurPad);
	if (auteur.status !== 200) {
		meld('FOUT', auteurPad, `auteurspagina niet op te halen (${auteur.status})`);
	} else {
		const h = auteur.html;
		if (telH1(h) !== 1) meld('FOUT', auteurPad, `${telH1(h)} H1 (verwacht 1)`);
		if (!/class="author-card"/i.test(h)) meld('FOUT', auteurPad, 'geen auteurskaart');
		if (!/class="author-card-photo"/i.test(h)) meld('FOUT', auteurPad, 'geen auteursfoto (of initialen)');
		if (!/class="author-card-bio"/i.test(h) && !/class="author-card-role"/i.test(h)) {
			meld('WAARSCHUWING', auteurPad, 'geen bio of functie — vul het auteursprofiel');
		}
		if (!/"@type":"ProfilePage"/.test(h)) meld('FOUT', auteurPad, 'geen ProfilePage-schema');
		if (!/"@type":"Person"/.test(h)) meld('FOUT', auteurPad, 'geen Person-schema');
	}
}

/* ---- Servicepagina ------------------------------------------------------ */

if (!dienstPad) {
	meld('FOUT', '(geen)', 'geen servicepagina gevonden om te controleren');
}

const dienst = dienstPad ? await haal(dienstPad) : { status: 0, html: '' };
if (dienstPad && dienst.status !== 200) {
	meld('FOUT', dienstPad, `servicepagina niet op te halen (${dienst.status})`);
} else if (dienstPad) {
	const h = dienst.html;
	if (telH1(h) !== 1) meld('FOUT', dienstPad, `${telH1(h)} H1 (verwacht 1)`);

	// Meerdere CTA's in de tekst: minstens twee losse CTA-blokken + de knoppen.
	const ctaBlokken = (h.match(/class="cta cta-/g) || []).length;
	if (ctaBlokken < 2) meld('FOUT', dienstPad, `slechts ${ctaBlokken} CTA-blok(ken); een servicepagina hoort er meerdere te hebben`);

	// SEO-tekst en koppenstructuur.
	if (!/<meta name="description"/i.test(h)) meld('FOUT', dienstPad, 'geen meta-description');
	if ((h.match(/<h2[\s>]/g) || []).length < 1) meld('WAARSCHUWING', dienstPad, 'geen H2 — is er wel tekst met koppen?');
}

/* ---- Productcategorie (alleen als de module aanstaat) ------------------- */

const prodcat = await haal('/producten/categorie/demo-productcategorie/');
if (prodcat.status === 200) {
	const h = prodcat.html;
	if (telH1(h) !== 1) meld('FOUT', '/producten/categorie/demo-productcategorie/', `${telH1(h)} H1 (verwacht 1)`);
	if (!/class="product-grid"/i.test(h)) meld('FOUT', '/producten/categorie/demo-productcategorie/', 'geen productoverzicht');
} else {
	console.log('Productcatalogus staat uit (of geen demo-categorie) — productcategorie overgeslagen.');
}

const fouten = meldingen.filter((m) => m.ernst === 'FOUT');
const waarschuwingen = meldingen.filter((m) => m.ernst === 'WAARSCHUWING');

console.log('');
for (const m of waarschuwingen) console.log(`WAARSCHUWING  ${m.waar}  ${m.tekst}`);
for (const m of fouten) console.error(`FOUT          ${m.waar}  ${m.tekst}`);
console.log('');
console.log(fouten.length === 0 ? 'Must-haves: geen fouten.' : `Must-haves: ${fouten.length} fout(en).`);

process.exit(fouten.length > 0 ? 1 : 0);
