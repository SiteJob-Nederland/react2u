/**
 * Gestructureerde data die Google anders vraagt dan de kit vroeger maakte.
 *
 * - Een BreadcrumbList heeft minstens twee ListItems
 *   (https://developers.google.com/search/docs/appearance/structured-data/breadcrumb).
 *   De homepage heeft daarom geen kruimelpad en geen BreadcrumbList; een
 *   pagina die naar een kruimelpad verwijst, moet het ook in de graaf hebben.
 * - De sitelinks-zoekbalk toont Google sinds 21 november 2024 niet meer; de
 *   documentatie verdween op 29 november 2024
 *   (https://developers.google.com/search/blog/2024/10/sitelinks-search-box,
 *   https://developers.google.com/search/updates). Een SearchAction levert dus
 *   niets op; het thema maakt hem niet meer.
 * - "Widely distributed links in the footers or templates of various sites"
 *   zijn link spam (https://developers.google.com/search/docs/essentials/spam-policies).
 *   "Gemaakt door SiteJob" heeft daarom rel="nofollow".
 *
 * Plugins nesten knopen soms (een BreadcrumbList onder WebPage.breadcrumb, een
 * WebSite onder isPartOf), of zetten een JSON-LD-blok als array neer. Daarom
 * kijken deze controles naar elk object, op elke diepte.
 */
const typen = (knoop) => [knoop?.['@type']].flat().filter((type) => typeof type === 'string');

/** Alle objecten in de JSON-LD, op elke diepte. */
export function alleKnopen(grafen) {
	const knopen = [];
	const loop = (waarde) => {
		if (Array.isArray(waarde)) {
			for (const item of waarde) loop(item);
			return;
		}
		if (!waarde || typeof waarde !== 'object') return;
		knopen.push(waarde);
		for (const kind of Object.values(waarde)) loop(kind);
	};
	loop(grafen);
	return knopen;
}

/** Geeft per probleem met het kruimelpad in de JSON-LD één regel tekst terug. */
export function kruimelpadProblemen(grafen, { minimum = 2 } = {}) {
	const knopen = alleKnopen(grafen);
	// Een @id bestaat als een knoop mét type het draagt; een losse { "@id": … } is alleen een verwijzing.
	const ids = new Set(knopen.filter((knoop) => knoop['@id'] && typen(knoop).length).map((knoop) => knoop['@id']));
	const problemen = [];
	for (const knoop of knopen) {
		if (typen(knoop).includes('BreadcrumbList')) {
			const niveaus = [knoop.itemListElement].flat().filter(Boolean).length;
			if (niveaus < 2) problemen.push(`BreadcrumbList heeft ${niveaus} ListItem(s); Google vraagt er minstens twee, laat hem anders weg`);
			else if (niveaus < minimum) problemen.push(`BreadcrumbList heeft ${niveaus} niveaus (verwacht minimaal ${minimum})`);
		}
		const verwijzing = knoop.breadcrumb;
		if (verwijzing?.['@id'] && !typen(verwijzing).length && !ids.has(verwijzing['@id'])) {
			problemen.push(`${typen(knoop).join('/') || 'een knoop'} verwijst naar kruimelpad ${verwijzing['@id']}, maar dat staat niet in de graaf`);
		}
	}
	return problemen;
}

/** Staat er ergens een SearchAction voor de verdwenen sitelinks-zoekbalk? */
export function heeftSearchAction(grafen) {
	return alleKnopen(grafen).some((knoop) => typen(knoop).includes('SearchAction'));
}

/**
 * De <a>-tags naar sitejob.nl zonder nofollow of sponsored. HTML-commentaar
 * telt niet mee; subdomeinen (zoals een staging) vallen erbuiten.
 */
export function volgendeSitejobLinks(html) {
	const links = [];
	for (const [tag] of html.replace(/<!--[\s\S]*?-->/g, '').matchAll(/<a\b(?:[^>"']|"[^"]*"|'[^']*')*>/gi)) {
		const attribuut = (naam) => tag.match(new RegExp(`\\s${naam}\\s*=\\s*(?:"([^"]*)"|'([^']*)'|([^\\s>]+))`, 'i'))?.slice(1).find((w) => w !== undefined);
		if (!/^https?:\/\/(www\.)?sitejob\.nl(?=[/?#]|$)/i.test(attribuut('href') ?? '')) continue;
		const rel = (attribuut('rel') ?? '').toLowerCase().split(/\s+/);
		if (!rel.includes('nofollow') && !rel.includes('sponsored')) links.push(tag);
	}
	return links;
}
