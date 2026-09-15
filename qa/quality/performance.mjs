/** Mobiele labmetingen; mediaan van drie koude navigaties per vertegenwoordigd paginatype. */
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { configuration, dependency, save } from './config.mjs';
import { browserArgs } from '../qa-resolve.mjs';
const { chromium } = dependency('playwright');
const puppeteer = dependency('puppeteer-core');
const lighthouse = dependency('lighthouse').default;
const config = await configuration();
const failures = [], results = [];
const median = numbers => numbers.slice().sort((a, b) => a - b)[Math.floor(numbers.length / 2)];
const selected = new Map();
for (const p of config.performancePaths || ['/']) selected.set(new URL(p, config.base).href, 'expliciet');
try {
  const surface = JSON.parse(await readFile(path.join(config.reportDir, 'surface.json'), 'utf8'));
  if (surface.site === config.base.origin && Date.now() - Date.parse(surface.date) < 86400000) {
    for (const [type, pattern] of [['artikel', /\bsingle\b/], ['auteur', /\bauthor\b/], ['overzicht', /\b(?:blog|post-type-archive)\b/], ['pagina', /\bpage\b/]]) {
      const page = surface.pages.find(p => p.width === 390 && pattern.test(p.bodyClasses) && new URL(p.url).pathname !== '/');
      if (page) selected.set(page.url, type);
    }
  }
} catch { /* Een losse performance-run gebruikt de expliciet opgegeven paden. */ }
if (!selected.size) throw Error('Minimaal één performancepagina vereist.');
for (const [url, type] of selected) {
  if (new URL(url).origin !== config.base.origin) throw Error('Performancepad buiten SITE_URL.');
  const runs = [];
  for (let i = 0; i < 3; i++) {
    let browser;
    try {
      browser = await puppeteer.launch({ executablePath: chromium.executablePath(), headless: true, args: browserArgs() });
      const page = await browser.newPage();
      await page.setRequestInterception(true);
      page.on('request', request => {
        if (request.isInterceptResolutionHandled()) return;
        if (!['GET', 'HEAD', 'OPTIONS'].includes(request.method())) return request.abort();
        const headers = { ...request.headers() };
        if (process.env.QA_HTTP_PASSWORD && new URL(request.url()).origin === config.base.origin) {
          headers.authorization = 'Basic ' + Buffer.from(`${process.env.QA_HTTP_USER || 'sitejob'}:${process.env.QA_HTTP_PASSWORD}`).toString('base64');
        }
        return request.continue({ headers });
      });
      const { lhr } = await lighthouse(url, { output: 'json', logLevel: 'error', onlyCategories: ['performance'], formFactor: 'mobile', throttlingMethod: 'simulate' }, undefined, page);
      if (lhr.runtimeError) throw Error(lhr.runtimeError.message);
      if (new URL(lhr.finalDisplayedUrl || lhr.finalUrl).href !== new URL(url).href) throw Error('Lighthouse meet een redirectdoel.');
      const metrics = { lcp: lhr.audits['largest-contentful-paint'].numericValue, cls: lhr.audits['cumulative-layout-shift'].numericValue,
        tbt: lhr.audits['total-blocking-time'].numericValue, performance: lhr.categories.performance.score };
      if (Object.values(metrics).some(v => typeof v !== 'number' || !Number.isFinite(v))) throw Error('Een vereiste meting ontbreekt.');
      runs.push({ metrics, lighthouseVersion: lhr.lighthouseVersion, warnings: lhr.runWarnings, diagnostics: Object.fromEntries(['lcp-breakdown-insight', 'lcp-discovery-insight', 'image-delivery-insight'].filter(id => lhr.audits[id]).map(id => [id, lhr.audits[id].details])),
        opportunities: Object.entries(lhr.audits).filter(([, a]) => a.score !== null && a.score < 1 && (a.details?.type === 'opportunity' || a.metricSavings)).map(([id, a]) => ({ id, title: a.title, displayValue: a.displayValue, metricSavings: a.metricSavings })) });
      console.log(`MEET ${url} ${i + 1}/3: LCP ${Math.round(metrics.lcp)}ms, CLS ${metrics.cls.toFixed(3)}, TBT ${Math.round(metrics.tbt)}ms, score ${Math.round(metrics.performance * 100)}`);
    } catch (error) { failures.push({ url, run: i + 1, message: error.message }); }
    finally { if (browser) await browser.close().catch(error => failures.push({ url, message: error.message })); }
  }
  if (runs.length !== 3) { failures.push({ url, message: 'Drie geldige metingen vereist.' }); continue; }
  const metrics = Object.fromEntries(Object.keys(config.budget).map(key => [key, median(runs.map(run => run.metrics[key]))]));
  for (const [key, threshold] of Object.entries(config.budget)) {
    if (key === 'performance' ? metrics[key] < threshold : metrics[key] > threshold) failures.push({ url, metric: key, actual: metrics[key], threshold });
  }
  results.push({ url, type, median: metrics, runs });
}
await save(config, 'performance', { passed: !failures.length, kind: 'Lighthouse mobiel, gesimuleerde verbinding/CPU, drie koude runs', budget: config.budget, results, failures,
  limitation: 'Labdata is geen CrUX/velddata. TBT is een labmaat en bewijst geen INP. Beoordeel LCP/INP/CLS bij voldoende echte bezoekers afzonderlijk.' });
for (const failure of failures) console.error(JSON.stringify(failure));
process.exitCode = failures.length ? 1 : 0;
