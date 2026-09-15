/** Gedeeld QA-contract. Een externe host vraagt altijd een expliciete omgeving. */
import { readFile, mkdir, writeFile, readdir } from 'node:fs/promises';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';
export const version = '1.0.0';
export const qaDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
export function dependency(name) {
  try { return createRequire(path.join(qaDir, 'package.json'))(name); }
  catch (error) {
    if (error.code !== 'MODULE_NOT_FOUND') throw error;
    // Ontwikkelgemak binnen de werkmap; losse klantprojecten gebruiken hun eigen npm install.
    for (const relative of ['../../../scripts/package.json', '../../scripts/package.json']) {
      try { return createRequire(path.resolve(qaDir, relative))(name); } catch (fallback) { if (fallback.code !== 'MODULE_NOT_FOUND') throw fallback; }
    }
    throw error;
  }
}
export async function configuration() {
  const saved = JSON.parse(await readFile(path.join(qaDir, 'quality.config.json'), 'utf8'));
  const base = new URL(process.env.SITE_URL || saved.localUrl);
  const local = ['127.0.0.1', 'localhost', '[::1]'].includes(base.hostname);
  const environment = process.env.QA_ENV || (local ? 'local' : '');
  if (!['local', 'staging', 'production'].includes(environment)) throw Error('Geef QA_ENV=staging of production voor een externe host.');
  if (environment === 'local' && !local) throw Error('QA_ENV=local mag alleen localhost benaderen.');
  if (!local && base.protocol !== 'https:') throw Error('Externe QA vereist HTTPS.');
  if (base.username || base.password || base.search || base.hash || base.pathname !== '/') throw Error('SITE_URL moet een origin zonder credentials, pad of query zijn.');
  let indexable = environment === 'production' || (environment === 'local' && saved.localIndexable === true);
  if (process.env.EXPECT_INDEXABLE !== undefined) {
    if (!['0', '1'].includes(process.env.EXPECT_INDEXABLE)) throw Error('EXPECT_INDEXABLE moet 0 of 1 zijn.');
    indexable = process.env.EXPECT_INDEXABLE === '1';
  }
  if (environment === 'staging' && indexable) throw Error('Staging blijft noindex.');
  if (environment === 'production' && !indexable) throw Error('Productiecontrole vereist indexeerbaarheid.');
  const config = { ...saved, base, environment, indexable, maxPages: saved.maxPages ?? 500,
    paths: process.env.QA_PATHS?.split(',').filter(Boolean) || saved.paths || ['/'],
    noindexPaths: process.env.QA_NOINDEX_PATHS?.split(',').filter(Boolean) || saved.noindexPaths || [],
    reportDir: path.resolve(process.env.QA_OUTPUT || path.join(qaDir, 'uitvoer/quality', environment)),
    budget: { lcp: 2500, cls: 0.1, tbt: 200, performance: 0.9, ...(saved.budget || {}) },
  };
  if (!Number.isInteger(config.maxPages) || config.maxPages < 1) throw Error('maxPages moet een positief geheel getal zijn.');
  for (const [metric, value] of Object.entries(config.budget)) {
    if (!['lcp', 'cls', 'tbt', 'performance'].includes(metric) || !Number.isFinite(value) || value < 0 || (['cls', 'performance'].includes(metric) && value > 1)) throw Error(`Ongeldig budget: ${metric}.`);
  }
  config.privatePaths = saved.privatePaths || [];
  config.sourceHash = await sourceHash();
  await mkdir(config.reportDir, { recursive: true });
  return config;
}
export async function save(config, name, report) {
  await writeFile(path.join(config.reportDir, name + '.json'), JSON.stringify({ version, date: new Date().toISOString(), environment: config.environment, site: config.base.origin, sourceHash: config.sourceHash, ...report }, null, 2) + '\n');
}
export function maxAge(headers) {
  return Number((headers['cache-control'] || '').match(/(?:^|,)\s*max-age\s*=\s*"?(\d+)/i)?.[1] || 0);
}
export function headerFailures(headers, { https = false, staging = false, asset = false } = {}) {
  const failures = [];
  if ((headers['x-content-type-options'] || '').toLowerCase() !== 'nosniff') failures.push('X-Content-Type-Options moet nosniff zijn.');
  if (!headers['referrer-policy']) failures.push('Referrer-Policy ontbreekt.');
  if (!headers['permissions-policy']) failures.push('Permissions-Policy ontbreekt.');
  const csp = headers['content-security-policy'] || '';
  for (const directive of ['base-uri', 'object-src', 'frame-ancestors']) {
    if (!new RegExp(`(?:^|;)\\s*${directive}\\s+[^;]+`, 'i').test(csp)) failures.push(`CSP mist ${directive}.`);
  }
  if (https && !/max-age\s*=\s*[1-9]\d*/i.test(headers['strict-transport-security'] || '')) failures.push('HSTS ontbreekt of is uitgeschakeld.');
  if (staging && !/\bnoindex\b/i.test(headers['x-robots-tag'] || '')) failures.push('Staging mist X-Robots-Tag: noindex op serverniveau.');
  const cc = headers['cache-control'] || '';
  if (asset && (maxAge(headers) < 3600 || /no-store|no-cache/i.test(cc))) failures.push('Statisch bestand mist bruikbare browsercache van minimaal één uur.');
  if (!asset && (/immutable/i.test(cc) || maxAge(headers) > 300)) failures.push('Dynamische response heeft het cachebeleid van statische bestanden.');
  return failures;
}
export function canonicalUrl(value) { const u = new URL(value); u.hash = ''; return u.href; }

/** Bind bewijs aan de broncode, configuratie en assets; rapporten wijzigen de hash niet. */
export async function sourceHash() {
  const root = path.resolve(qaDir, '..'), hash = createHash('sha256');
  async function visit(relative) {
    const dir = path.join(root, relative);
    let entries; try { entries = await readdir(dir, { withFileTypes: true }); } catch (error) { if (error.code === 'ENOENT') return; throw error; }
    for (const entry of entries.sort((a, b) => a.name.localeCompare(b.name, 'en'))) {
      if (entry.isSymbolicLink() || ['node_modules', '.git', 'uitvoer'].includes(entry.name)) continue;
      const file = path.join(relative, entry.name);
      if (entry.isDirectory()) await visit(file);
      else if (entry.isFile()) { hash.update(file + '\0'); hash.update(await readFile(path.join(root, file))); }
    }
  }
  for (const dir of ['theme', 'themes', 'plugins', 'deploy/quality', 'qa/quality']) await visit(dir);
  hash.update(await readFile(path.join(qaDir, 'quality.config.json')));
  // Optionele integraties en hun intakekeuzes horen bij dezelfde bewijsversie.
  for (const relative of ['brief/06-opties.json', 'qa/qa-opties.mjs', 'qa/package.json', 'qa/package-lock.json']) {
    try { hash.update(relative + '\0'); hash.update(await readFile(path.join(root, relative))); }
    catch (error) { if (error.code !== 'ENOENT') throw error; }
  }
  return hash.digest('hex');
}
export function manualFailures(manual, config, now = Date.now()) {
  const required = ['keyboard', 'focus', 'screenreader', 'contrastIncomplete', 'content', 'forms', 'migration', 'contentPublication'];
  if (config.croRequired === true) required.push('conversionRoute', 'conversionDelivery', 'conversionConsent');
  const date = Date.parse(manual.date), failures = [];
  if (manual.site !== config.base.origin || manual.version !== version || manual.sourceHash !== config.sourceHash) failures.push('Handmatig bewijs hoort bij een andere site, code of kwaliteitsversie.');
  if (!manual.reviewer?.trim() || !Number.isFinite(date) || date > now || now - date > 7 * 86400000) failures.push('Beoordelaar/datum ontbreekt of bewijs is verlopen.');
  for (const key of required) if (!['pass', 'not-applicable'].includes(manual.checks?.[key]?.status) || !manual.checks[key].evidence?.trim()) failures.push(`Handmatig bewijs ontbreekt: ${key}.`);
  return failures;
}
