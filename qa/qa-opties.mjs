/** Alleen lokale intakekeuzes controleren; niets activeren of verbinden. */
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
export function validateOptions(options) {
  const errors = [];
  if (options?.schemaVersion !== undefined && ![1, 2].includes(options.schemaVersion)) errors.push('Opties: onbekende schemaversie.');
  for (const name of ['crm', 'analytics', 'searchConsole']) {
    const item = options?.[name];
    const deferredCrm = name === 'crm' && options?.schemaVersion === 2 && item?.choice === 'deferred';
    if (!deferredCrm && !['yes', 'no'].includes(item?.choice)) errors.push(`${name}: vraag de klant en leg ja of nee vast in brief/06-opties.json.`);
    if (typeof item?.evidence !== 'string' || !item.evidence.trim()) errors.push(`${name}: leg vast wanneer/waar de keuze is gemaakt.`);
  }
  const crm = options?.crm;
  if (crm?.choice === 'yes') {
    if (!['local', 'api'].includes(crm.mode)) errors.push('CRM: kies local of api.');
    if (!Number.isInteger(crm.retentionDays) || crm.retentionDays < 1 || crm.retentionDays > 3650) errors.push('CRM: spreek een bewaartermijn in dagen af.');
    if (crm.mode === 'api' && !crm.provider?.trim()) errors.push('CRM: benoem de ontvanger/tussenlaag die het universele contract verwerkt.');
  } else if (crm?.mode !== 'off') errors.push('CRM: zonder ja hoort mode op off te staan.');
  if (options?.analytics?.choice === 'yes' && !options.analytics.provider?.trim()) errors.push('Analytics: benoem de provider.');
  if (options?.searchConsole?.choice === 'yes' && !options.searchConsole.property?.trim()) errors.push('Search Console: leg de geverifieerde property vast.');
  const privacy = options?.privacy;
  if (privacy?.reviewed !== true || !['required', 'not-required'].includes(privacy?.banner) || !privacy?.evidence?.trim()) errors.push('Privacy/cookies: vul de verplichte controle en onderbouwing uit brief/08-privacy-en-cookies.md in.');
  for (const key of ['privacyPath', 'cookiesPath']) {
    if (typeof privacy?.[key] !== 'string' || !/^\/(?!\/)[^\s]*$/.test(privacy[key])) errors.push(`Privacy/cookies: een sitepad voor ${key} ontbreekt.`);
  }
  if (privacy?.banner === 'required' && !privacy?.cmp?.trim()) errors.push('Cookiebanner: benoem de gekozen CMP/oplossing.');
  if (options?.analytics?.choice === 'yes' && options.analytics.provider?.toLowerCase() === 'ga4' && privacy?.banner !== 'required') errors.push('GA4: de kit vereist een toestemmingsbanner.');
  if (options?.schemaVersion === 2 || options?.croPlatform !== undefined) errors.push(...validateCroPlatform(options.croPlatform, privacy));
  return errors;
}

/** Valideert intake, niet of het toekomstige platform een site heeft ontvangen. */
export function validateCroPlatform(platform, privacy) {
  const errors = [];
  const filled = value => typeof value === 'string' && value.trim().length > 0;
  if (!platform || typeof platform !== 'object' || Array.isArray(platform)) return ['CRO-platform: vul de verplichte intake in brief/06-opties.json in.'];
  if (!/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(platform.siteKey || '')) errors.push('CRO-platform: de vaste siteKey uit de scaffolder ontbreekt of is ongeldig.');
  if (!filled(platform.companyReference)) errors.push('CRO-platform: leg de geverifieerde bedrijfstoewijzing vast.');
  if (platform.registration !== 'automatic') errors.push('CRO-platform: nieuwe websites gebruiken automatische registratie na de intake.');
  for (const key of ['cro', 'sessionReplay', 'monthlyReview', 'aiAnalysis']) {
    const option = platform[key];
    if (!['yes', 'no'].includes(option?.choice) || !filled(option?.evidence)) errors.push(`CRO-platform ${key}: vraag ja/nee en leg de bron vast.`);
  }
  const provider = platform.measurement?.provider;
  if (!['none', 'posthog'].includes(provider) || !filled(platform.measurement?.evidence)) errors.push('CRO-platform: kies en onderbouw PostHog of geen; andere bronnen zijn vervolgwerk.');
  if (platform.cro?.choice === 'no' && (provider !== 'none' || ['sessionReplay', 'monthlyReview', 'aiAnalysis'].some(key => platform[key]?.choice === 'yes'))) errors.push('CRO-platform: zonder CRO-keuze blijven CRO-meting, opnames, maandreview en AI uit.');
  if (platform.cro?.choice === 'yes' && provider === 'none') errors.push('CRO-platform: kies voor de eerste CRO-route een meetbron.');
  if (provider === 'posthog' && privacy?.banner !== 'required') errors.push('CRO-platform: gekozen meting vereist de toestemmingsbanner uit het kitcontract.');
  try { new Intl.DateTimeFormat('nl-NL', { timeZone: platform.reviewTimezone }).format(); }
  catch { errors.push('CRO-platform: de reviewtijdzone is ongeldig.'); }
  if (!filled(platform.reviewTimezone)) errors.push('CRO-platform: leg de reviewtijdzone vast.');
  return errors;
}
if (process.argv[1] === fileURLToPath(import.meta.url)) {
  const errors = validateOptions(JSON.parse(await readFile(new URL('../brief/06-opties.json', import.meta.url), 'utf8')));
  errors.forEach(e => console.error(e));
  if (!errors.length) console.log('Intakekeuzes zijn vastgelegd. Dit controleert geen dashboardverbinding of trackerinstallatie.');
  process.exitCode = errors.length ? 1 : 0;
}
