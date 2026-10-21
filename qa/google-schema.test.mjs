import test from 'node:test';
import assert from 'node:assert/strict';
import { kruimelpadProblemen, heeftSearchAction, volgendeSitejobLinks } from './google-schema.mjs';

const site = 'https://voorbeeld.test/';
const kruimel = (positie, naam, url) => ({ '@type': 'ListItem', position: positie, name: naam, ...(url ? { item: url } : {}) });
const kruimels = (url, ...items) => ({ '@type': 'BreadcrumbList', '@id': `${url}#breadcrumbs`, itemListElement: items });
const pagina = (url, metKruimelpad) => ({ '@type': 'WebPage', '@id': `${url}#webpage`, url, ...(metKruimelpad ? { breadcrumb: { '@id': `${url}#breadcrumbs` } } : {}) });
const graaf = (...knopen) => [{ '@context': 'https://schema.org', '@graph': knopen }];
const website = (extra = {}) => ({ '@type': 'WebSite', '@id': `${site}#website`, url: site, name: 'Voorbeeld', ...extra });

test('homepage zonder kruimelpad is in orde', () => assert.deepEqual(kruimelpadProblemen(graaf(website(), pagina(site, false))), []));
test('kruimelpad met alleen Home is een fout', () => assert.match(kruimelpadProblemen(graaf(pagina(site, true), kruimels(site, kruimel(1, 'Home', site))))[0], /1 ListItem\(s\); Google vraagt er minstens twee/));
test('overzicht onder Home met twee kruimels is in orde', () => {
	const blog = `${site}blog/`;
	assert.deepEqual(kruimelpadProblemen(graaf(pagina(blog, true), kruimels(blog, kruimel(1, 'Home', site), kruimel(2, 'Blog')))), []);
});
test('een hoger minimum blijft gelden', () => {
	const blog = `${site}blog/`;
	assert.deepEqual(kruimelpadProblemen(graaf(kruimels(blog, kruimel(1, 'Home', site), kruimel(2, 'Blog'))), { minimum: 3 }), ['BreadcrumbList heeft 2 niveaus (verwacht minimaal 3)']);
});
test('verwijzing naar een kruimelpad dat ontbreekt is een fout', () => assert.match(kruimelpadProblemen(graaf(pagina(site, true)))[0], /WebPage verwijst naar kruimelpad .*#breadcrumbs, maar dat staat niet in de graaf/));
test('SearchAction in de WebSite wordt herkend', () => {
	const zoeken = { '@type': 'SearchAction', target: { '@type': 'EntryPoint', urlTemplate: `${site}?s={search_term_string}` }, 'query-input': 'required name=search_term_string' };
	assert.equal(heeftSearchAction(graaf(website({ potentialAction: zoeken }))), true);
	assert.equal(heeftSearchAction(graaf(website({ potentialAction: [{ '@type': 'ReadAction' }, zoeken] }))), true);
	assert.equal(heeftSearchAction(graaf(website())), false);
	// Ook in een geneste WebSite, zoals plugins die onder isPartOf zetten.
	assert.equal(heeftSearchAction(graaf({ '@type': 'WebPage', isPartOf: website({ potentialAction: zoeken }) })), true);
});

// Vormen die plugins maken: genest, zonder @id, één item als object, of een array als blok.
const blog = `${site}blog/`;
test('genest kruimelpad met twee items onder WebPage.breadcrumb is in orde', () => {
	assert.deepEqual(kruimelpadProblemen(graaf({ ...pagina(blog, false), breadcrumb: kruimels(blog, kruimel(1, 'Home', site), kruimel(2, 'Blog')) })), []);
});
test('genest kruimelpad met één item zonder @id is een fout', () => {
	const enkel = { '@type': 'BreadcrumbList', itemListElement: [kruimel(1, 'Home', site)] };
	assert.match(kruimelpadProblemen(graaf({ ...pagina(site, false), breadcrumb: enkel })).join(), /1 ListItem\(s\)/);
});
test('itemListElement als los object telt als één item', () => {
	assert.match(kruimelpadProblemen(graaf({ ...kruimels(site), itemListElement: kruimel(1, 'Home', site) })).join(), /1 ListItem\(s\)/);
});
test('volgende links naar sitejob.nl worden gevonden, nofollow en sponsored niet', () => {
	const fout = [
		'<a href="https://sitejob.nl/">SiteJob</a>',
		'<a href="http://www.sitejob.nl?utm_source=voet">SiteJob</a>',
		'<a href="https://sitejob.nl#x" data-rel="nofollow">SiteJob</a>',
		'<a rel="nofollow-x" href="https://sitejob.nl/">SiteJob</a>',
		'<a title="a > b" href=\'https://sitejob.nl/diensten/\'>SiteJob</a>',
	];
	for (const html of fout) assert.equal(volgendeSitejobLinks(html).length, 1, html);
	const goed = [
		'<a href="https://sitejob.nl/" rel="nofollow">SiteJob</a>',
		'<a rel="noopener sponsored" href="https://sitejob.nl/">SiteJob</a>',
		'<a href=https://sitejob.nl/ rel=nofollow>SiteJob</a>',
		'<a data-href="https://sitejob.nl/" href="/contact/">x</a>',
		'<!-- <a href="https://sitejob.nl/">SiteJob</a> -->',
		'<a href="https://staging.klant.sitejob.nl/">x</a><a href="https://sitejob.nl.voorbeeld.test/">y</a>',
	];
	for (const html of goed) assert.deepEqual(volgendeSitejobLinks(html), [], html);
});
test('een JSON-LD-blok als array wordt ook gelezen', () => {
	assert.match(kruimelpadProblemen([[{ '@context': 'https://schema.org', ...kruimels(site, kruimel(1, 'Home', site)) }]]).join(), /1 ListItem\(s\)/);
	assert.equal(heeftSearchAction([[website({ potentialAction: { '@type': 'SearchAction' } })]]), true);
});
