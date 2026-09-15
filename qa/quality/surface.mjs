/** Volledige interne crawl + sitemap, twee schermmaten, axe, headers en assetbeleid. */
import { configuration, dependency, save, headerFailures, canonicalUrl } from './config.mjs';
import { browserArgs } from '../qa-resolve.mjs';
const { chromium } = dependency('playwright');
const axe = dependency('axe-core');
const { XMLParser, XMLValidator } = dependency('fast-xml-parser');
const config = await configuration();
const failures = [], warnings = [], pages = [], sitemaps = [], assets = new Map();
const iconUrls = new Set();
const fail = (url, message, details) => failures.push({ url, message, ...(details ? { details } : {}) });
const browser = await chromium.launch({ args: browserArgs() });
const context = await browser.newContext({ reducedMotion: 'reduce',
  ...(process.env.QA_HTTP_PASSWORD ? { httpCredentials: { username: process.env.QA_HTTP_USER || 'sitejob', password: process.env.QA_HTTP_PASSWORD, origin: config.base.origin } } : {}) });
// Ook per ongeluk geladen clientcode mag tijdens QA geen formulier/API-write uitvoeren.
await context.route('**/*', route => ['GET', 'HEAD', 'OPTIONS'].includes(route.request().method()) ? route.continue() : route.abort());
const get = url => context.request.get(url, { maxRedirects: 0, timeout: 20000 });
const queue = new Set(config.paths.map(p => new URL(p, config.base).href));
const sitemapUrls = new Set(), visited = new Set(), redirects = new Map();
let complete = true;
const xmlParser = new XMLParser({ ignoreAttributes: false, processEntities: true });
const list = value => value === undefined ? [] : Array.isArray(value) ? value : [value];
const sameSite = value => { try { return new URL(value).origin === config.base.origin; } catch { return false; } };
try {
  const robotsUrl = new URL('/robots.txt', config.base).href;
  const robots = await get(robotsUrl);
  const text = await robots.text();
  if (robots.status() !== 200) fail(robotsUrl, `robots.txt geeft ${robots.status()}.`);
  const mapQueue = [...text.matchAll(/^Sitemap:\s*(\S+)/gmi)].map(m => m[1]);
  if (config.indexable && !mapQueue.length) fail(robotsUrl, 'Geen Sitemap-regel.');
  // Evalueer groepen: een blokkade voor een specifieke bot is geen algemene blokkade.
  if (config.indexable) {
    let agents = [], rulesStarted = false;
    for (const line of text.split(/\r?\n/).map(s => s.replace(/#.*/, '').trim()).filter(Boolean)) {
      const agent = line.match(/^User-agent:\s*(.+)$/i);
      if (agent) { if (rulesStarted) agents = []; agents.push(agent[1].toLowerCase()); rulesStarted = false; continue; }
      if (/^(Allow|Disallow):/i.test(line)) {
        rulesStarted = true;
        if (agents.some(a => ['*', 'googlebot'].includes(a)) && /^Disallow:\s*\/$/i.test(line)) fail(robotsUrl, 'Publieke site blokkeert alle pagina’s voor zoekcrawlers.');
      }
    }
  }
  if (!mapQueue.length) mapQueue.push(new URL('/wp-sitemap.xml', config.base).href);
  const seen = new Set();
  while (mapQueue.length) {
    const url = mapQueue.shift();
    if (seen.has(url)) continue;
    seen.add(url);
    if (seen.size > 100) { complete = false; fail(url, 'Meer dan 100 sitemaps: controle onvolledig.'); break; }
    if (!sameSite(url)) { fail(url, 'Sitemap buiten de ingestelde origin.'); continue; }
    const response = await get(url);
    sitemaps.push({ url, status: response.status() });
    if (!config.indexable) {
      if (response.status() !== 404) fail(url, 'Niet-publieke omgeving geeft een publieke sitemap.');
      continue;
    }
    if (response.status() !== 200) { fail(url, `Sitemap moet rechtstreeks 200 geven, geeft ${response.status()}.`); continue; }
    const xml = await response.text();
    if (/<!DOCTYPE|<!ENTITY/i.test(xml) || XMLValidator.validate(xml) !== true) { fail(url, 'Ongeldige of niet-ondersteunde sitemap-XML.'); continue; }
    const parsed = xmlParser.parse(xml);
    if (parsed.sitemapindex) {
      const children = list(parsed.sitemapindex.sitemap);
      if (!children.length) fail(url, 'Lege sitemapindex.');
      for (const entry of children) {
        if (typeof entry.loc !== 'string' || !sameSite(entry.loc)) fail(url, 'Ongeldige deelsitemap-URL.');
        else mapQueue.push(entry.loc);
      }
    } else if (parsed.urlset) {
      const entries = list(parsed.urlset.url);
      if (!entries.length) fail(url, 'Lege URL-sitemap.');
      for (const entry of entries) {
        if (typeof entry.loc !== 'string' || !sameSite(entry.loc)) { fail(url, 'Ongeldige of externe pagina-URL.'); continue; }
        sitemapUrls.add(entry.loc); queue.add(entry.loc);
        if (entry.lastmod && !Number.isFinite(Date.parse(entry.lastmod))) fail(entry.loc, 'Ongeldige lastmod.');
      }
    } else fail(url, 'Geen sitemapindex/urlset gevonden.');
  }
  for (const url of queue) {
    if (visited.has(url)) continue;
    if (visited.size >= config.maxPages) { complete = false; fail(url, `Crawllimiet ${config.maxPages} bereikt: geen volledige acceptatie.`); break; }
    visited.add(url);
    if (!sameSite(url)) { fail(url, 'Testpad buiten de ingestelde origin.'); continue; }
    let raw;
    try { raw = await get(url); } catch (error) { fail(url, error.message); continue; }
    if (raw.status() >= 300 && raw.status() < 400) {
      if (sitemapUrls.has(url)) fail(url, `Redirect ${raw.status()} in sitemap.`);
      const target = raw.headers().location && new URL(raw.headers().location, url);
      if (!target || target.href === url) fail(url, 'Ongeldige of circulaire redirect.');
      else if (sameSite(target.href)) {
        redirects.set(url, target.href);
        const chain = new Set([url]); let next = target.href;
        while (redirects.has(next) && !chain.has(next)) { chain.add(next); next = redirects.get(next); }
        if (chain.has(next)) fail(url, 'Circulaire redirectketen.', [...chain, next]);
        queue.add(target.href);
        warnings.push({ url, message: `Interne link verwijst via ${raw.status()} naar ${target.href}.` });
      } else warnings.push({ url, message: 'Externe redirect handmatig beoordelen.', target: target.href });
      continue;
    }
    if (raw.status() !== 200) { fail(url, `Interne pagina geeft ${raw.status()}.`); continue; }
    if (!/text\/html/i.test(raw.headers()['content-type'] || '')) { fail(url, 'Verwachte HTML-pagina heeft een ander contenttype.'); continue; }
    for (const message of headerFailures(raw.headers(), { https: config.base.protocol === 'https:', staging: config.environment === 'staging' })) fail(url, message);
    for (const width of [390, 1440]) {
      const page = await context.newPage();
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      page.on('response', response => {
        if (['image', 'stylesheet', 'script', 'font'].includes(response.request().resourceType())) {
          if (response.status() >= 400) errors.push(`Asset ${response.status()}: ${response.url()}`);
          if (sameSite(response.url()) && response.status() === 200) assets.set(response.url(), response.request().resourceType());
        }
      });
      try {
        await page.setViewportSize({ width, height: 900 });
        const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        if (response.status() !== 200 || canonicalUrl(page.url()) !== canonicalUrl(url)) fail(url, 'Browser eindigt op een andere URL of status.');
        await page.evaluate(async () => {
          await Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 5000))]);
          for (let y = 0; y < Math.min(document.documentElement.scrollHeight, 60000); y += 800) {
            scrollTo(0, y); await new Promise(resolve => setTimeout(resolve, 30));
          }
          scrollTo(0, 0);
          await Promise.race([Promise.all([...document.images].filter(i => i.currentSrc).map(i => i.decode().catch(() => {}))), new Promise(resolve => setTimeout(resolve, 4000))]);
        });
        const facts = await page.evaluate(() => ({
          title: document.title, titleCount: document.querySelectorAll('title').length, h1: document.querySelectorAll('h1').length, main: document.querySelectorAll('main').length,
          description: [...document.querySelectorAll('meta[name="description"]')].map(e => e.content),
          canonical: [...document.querySelectorAll('link[rel="canonical"]')].map(e => e.href),
          robots: [...document.querySelectorAll('meta[name="robots"]')].map(e => e.content).join(','),
          overflow: document.documentElement.scrollWidth > innerWidth + 1,
          bodyClasses: document.body.className,
          links: [...document.querySelectorAll('a[href]')].map(a => a.href),
          schema: [...document.querySelectorAll('script[type="application/ld+json"]')].map(e => { try { return JSON.parse(e.textContent); } catch { return null; } }),
          icons: [...document.querySelectorAll('link[rel="icon"]')].map(e => e.href),
          images: [...document.images].filter(i => i.currentSrc && i.getBoundingClientRect().width > 0).map(i => ({ src: i.currentSrc, natural: i.naturalWidth, rendered: i.getBoundingClientRect().width, bytes: performance.getEntriesByName(i.currentSrc)[0]?.encodedBodySize || 0 })),
        }));
        const privatePage = config.privatePaths.includes(new URL(url).pathname);
        if (!facts.title.trim() || facts.titleCount !== 1) fail(url, `${width}: verwacht één ingevulde titel.`);
        if (facts.h1 !== 1 || facts.main !== 1) fail(url, `${width}: verwacht één H1/main; gevonden ${facts.h1}/${facts.main}.`);
        if (!privatePage && (facts.description.length !== 1 || !facts.description[0]?.trim())) fail(url, `${width}: verwacht één ingevulde description.`);
        if (!privatePage && (facts.canonical.length !== 1 || !sameSite(facts.canonical[0]))) fail(url, `${width}: ontbrekende, dubbele of externe canonical.`, facts.canonical);
        else if (sitemapUrls.has(url) && canonicalUrl(facts.canonical[0]) !== canonicalUrl(url)) fail(url, 'Sitemap bevat een niet-canonieke URL.', facts.canonical);
        const noindex = /\bnoindex\b/i.test(facts.robots + ',' + (raw.headers()['x-robots-tag'] || ''));
        const shouldIndex = config.indexable && !privatePage && !config.noindexPaths.includes(new URL(url).pathname);
        if (noindex === shouldIndex || (sitemapUrls.has(url) && noindex)) fail(url, `${width}: indexeerbaarheid wijkt af.`);
        if ((!privatePage && !facts.schema.length) || facts.schema.some(s => s === null)) fail(url, `${width}: ontbrekende/ongeldige JSON-LD.`);
        if (facts.overflow) fail(url, `${width}: horizontale overflow.`);
        for (const icon of facts.icons) if (sameSite(icon)) { iconUrls.add(icon); assets.set(icon, 'icon'); }
        for (const img of facts.images) {
          if (img.bytes > 50000 && img.natural > img.rendered * 2.5) fail(url, `${width}: onnodig groot beeld.`, img);
          if (img.bytes > 500000) fail(url, `${width}: beeld boven 500 kB.`, img);
        }
        await page.addScriptTag({ content: axe.source });
        const a11y = await page.evaluate(async () => {
          const { violations, incomplete } = await axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'] } });
          return { violations, incomplete };
        });
        for (const violation of a11y.violations) fail(url, `${width}: ${violation.id}`, violation.nodes.map(n => ({ target: n.target, summary: n.failureSummary, html: n.html })));
        for (const error of errors) fail(url, `${width}: ${error}`);
        pages.push({ url, width, ...facts, ...a11y, errors });
        for (const href of facts.links) {
          let next; try { next = new URL(href); } catch { continue; }
          const pagination = next.search && [...next.searchParams].every(([key, value]) => ['page', 'paged'].includes(key) && /^[1-9]\d*$/.test(value));
          if (!sameSite(href) || (next.search && !pagination) || /\/(?:wp-admin|wp-json|wp-login\.php|feed)(?:\/|$)/.test(next.pathname)
            || /\.(?:pdf|jpe?g|png|webp|avif|gif|svg|ico|zip|xml|txt|woff2?|mp4|webm)$/i.test(next.pathname)) continue;
          next.hash = ''; queue.add(next.href);
        }
        console.log(`CHECK ${width}px ${url} — axe ${a11y.violations.length}`);
      } catch (error) { fail(url, `${width}: ${error.message}`); }
      finally { await page.close(); }
    }
  }
  // Eén request per asset. Geen limiet die stilletjes een groen resultaat oplevert.
  for (const [url, type] of assets) {
    const response = await get(url);
    if (response.status() !== 200) fail(url, `Asset geeft ${response.status()}.`);
    if (iconUrls.has(url) && (await response.body()).length > 20000) fail(url, 'Browsericoon groter dan 20 KB; bouw een klein favicon.');
    for (const message of headerFailures(response.headers(), { asset: true, https: config.base.protocol === 'https:', staging: config.environment === 'staging' })) fail(url, message);
    if (['stylesheet', 'script'].includes(type) && /\/themes\//.test(url)
      && !/[?&]ver=/.test(url) && !/[.-][a-f0-9]{8,}\./.test(new URL(url).pathname)) fail(url, 'Thema-CSS/JS mist een bestandsversie.');
  }
  // Een foutpagina moet ook echt een foutstatus hebben; zoekresultaten blijven noindex.
  for (const [path, status] of [['/sitejob-qa-bestaat-niet-9f730e2/', 404], ['/?s=sitejobqa9f730e2', 200]]) {
    const url = new URL(path, config.base).href;
    const response = await get(url);
    if (response.status() !== status) fail(url, `Verwacht ${status}; gevonden ${response.status()}.`);
    const html = await response.text();
    if (status === 200 && !/noindex/i.test(html + (response.headers()['x-robots-tag'] || ''))) fail(url, 'Zoekresultaten missen noindex.');
  }
} catch (error) { fail(config.base.href, error.message); }
finally { await browser.close(); }
await save(config, 'surface', { passed: failures.length === 0, complete, sitemaps, pages, assets: [...assets.keys()], failures, warnings });
for (const item of failures) console.error(`${item.url}: ${item.message}`);
console.log(`${pages.length} schermcontroles, ${sitemaps.length} sitemaps, ${failures.length} fouten. Onbesliste axe-resultaten vragen handmatige beoordeling.`);
process.exitCode = failures.length ? 1 : 0;
